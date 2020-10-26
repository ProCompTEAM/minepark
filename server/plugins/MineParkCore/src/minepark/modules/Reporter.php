<?php
namespace minepark\modules;

use minepark\utils\CallbackTask;
use minepark\Core;
use minepark\Permission;

use pocketmine\Player;
use pocketmine\Server;

class Reporter
{
	public $playerReports;

	public $autoIncrement = 1;
	
	const CHARACTERS_LIMIT = 300;

	public function __construct()
	{
		$this->playerReports = [];
	}
	
	public function getCore() : Core
	{
		return Core::getActive();
	}
	
	public static function getPlayerId(Player $player) : string
	{
		return strtolower($player->getName());
	}
	
	public static function getHelpers() : ?array
	{
		$list = array();
		$server = Server::getInstance();
		foreach($server->getOnlinePlayers() as $plr) {
			if (self::isHelper($plr)) {
				$list[] = $plr;
			}
		}
		
		if (empty($list[0])) {
			return null;
		}
		
		return $list;
	}
	
	public function reportExists($reportId) : bool
	{
		return !empty($this->playerReports[$reportId]);
	}
	
	public function chooseRandomHelper() : ?string
	{
		$allHelpers = self::getHelpers();

		if ($allHelpers == null) {
			return null;
		}
		$helperCount = count($allHelpers) - 1;

		return $allHelpers[rand(0, $helperCount)]->getName();
	}
	
	public static function isHelper(Player $player) : bool
	{
		if ($player->hasPermission(Permission::ADMINISTRATOR_MODERATOR)) {
			return true;
		}

		if ($player->hasPermission(Permission::ADMINISTRATOR_HELPER)) {
			return true;
		}
		
		return false;
	}
	
	public function generateReportID() : int
	{
		$id = $this->autoIncrement;
		$this->autoIncrement++;
		
		if ($this->autoIncrement == 5001) {
			$this->autoIncrement = 1;
		}
	
		return $id;
	}
	
	public function createReport($reportId, Player $reporter, $reportContent)
	{
		$this->playerReports[$reportId] = [
			"reporter" => $reporter,
			"content" => $reportContent
		];
	}
	public static function symbolsMax($string) : bool
	{
		return strlen($string) > self::CHARACTERS_LIMIT;
	}

	public function replyReport(Player $replier, $reportId, $content) : bool
	{
		if (!$this->reportExists($reportId)) {
			$replier->sendMessage("§bДанного тикета не существует :(");
			return false;
		}

		if (self::symbolsMax($content)) {
			$replier->sendMessage("§bПревышено количество символов!");
			return false;
		}

		if (self::getHelpers() == null) {
			$replier->sendMessage("§bХелперы и модераторы §eне в сети. §bПожалуйста, попробуйте позже!");
			$this->closeReport($reportId);
			return false;
		}

		if (self::isHelper($replier)) {
			$this->helperReply($replier, $this->playerReports[$reportId], $content, $reportId);
		} else {
			$this->playerReply($replier, $this->playerReports[$reportId], $content, $reportId);
		}

		return true;
	}

	private function playerReply(Player $replier, $reportInfo, $reportContent, $reportId)
	{
		$reporter = $reportInfo['reporter'];

		if ($replier->getName() != $reporter->getName()) {
			$replier->sendMessage("§bВы не имеете доступ к данному репорту.");
			return true;
		}

		$replier->sendMessage("§bВы ответили на тикет §e".$reportId." §bхелперам с сообщением:");
		$replier->sendMessage("§b $reportContent");

		foreach(self::getHelpers() as $helper) {
			$helper->sendMessage("Игрок §e".$replier->getName()." §bответил §bна тикет ".$reportId." §bс сообщением:");
			$helper->sendMessage("§b $reportContent");
		}
	}
	private function helperReply(Player $replier, $reportInfo, $reportContent, $reportId)
	{
		$reporter = $reportInfo['reporter'];

		if ($replier->getName() == $reporter->getName()) {
			$replier->sendMessage("§bВы не можете отвечать§e САМОМУ СЕБЕ!");
			return false;
		}
			
		if (!$reporter->isOnline()) {
			$this->closeReport($reportId);
			$replier->sendMessage("§bДанный игрок больше §eНЕ в§b игре. Репорт закрыт.");
			return false;
		}
			
		$reporter->sendMessage("§bВам ответил хелпер §e".$replier->getName()." §b с содержанием:");
		$reporter->sendMessage("§b $reportContent");
		$reporter->sendMessage("§bЧто бы ответить на этот репорт, пропишите: /report reply $reportId <text>");

		$replier->sendMessage("§bВы ответили на тикет §e".$reportId." §bигроку §e ".$reporter->getName()." §b с сообщением:");
		$replier->sendMessage("§b $reportContent");

		foreach(self::getHelpers() as $helper) {
				
			if ($helper->getName() == $replier->getName()) {
				continue;
			}

			$helper->sendMessage("§Хелпер §e".$replier->getName()." §bответил игроку $reporter §bна тикет ".$reportId."§bс сообщением:");
			$helper->sendMessage("§b $reportContent");
		}
		return true;
	}

	public function closeReport($reportId) : bool
	{
		if (!$this->reportExists($reportId)) {
			return false;
		}

		$allHelpers = self::getHelpers();

		$reporter = $this->playerReports[$reportId]['reporter'];
		
		$this->playerReports[$reportId] = null;
		
		if ($allHelpers !== null) {
			foreach($allHelpers as $helper) {
				$helper->sendMessage("§bТикет §e $reportId §bбыл закрыт.");
			}
		}

		if (!$reporter->isOnline()) {
			return true;
		}

		$reporter->sendMessage("§bВаш тикет с айди §e".$reportId."§b был закрыт.");
		return true;
	}

	public function playerReport(Player $player, $reportContent)
	{
		if (self::getHelpers() == null) {
			$player->sendMessage("§bХелперы и модераторы §eне в сети. §bПожалуйста, попробуйте позже!");
			return;
		}
		
		if (self::symbolsMax($reportContent)) {
			$player->sendMessage("§bСлишком много символов в данном тикете. Попробуйте укоротить.");
			return;
		}
		
		$this->createNewReport($player, $reportContent);
	}

	private function createNewReport(Player $reporter, $reportContent)
	{
		$reportID = $this->generateReportID();

		$this->createReport($reportID, $reporter, $reportContent);
		
		$reporter->sendMessage("§bВы создали новый тикет с ID §e".$reportID." §bс содержанием:");
		$reporter->sendMessage("§b$reportContent");
		
		$helperName = $this->chooseRandomHelper();

		foreach(self::getHelpers() as $helper) {
			$helper->sendMessage("§bНужна помощь игроку §e".$reporter->getName()."§b. Айди тикета - §e$reportID. Содержание:");
			$helper->sendMessage("§b$reportContent");
			$helper->sendMessage("§bПусть ответит хелпер §e".$helperName);
		}
	}
}
?>