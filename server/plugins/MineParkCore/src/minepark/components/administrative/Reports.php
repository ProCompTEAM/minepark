<?php
namespace minepark\components\administrative;

use pocketmine\Server;

use minepark\defaults\Permissions;
use minepark\components\base\Component;
use minepark\common\player\MineParkPlayer;
use minepark\defaults\ComponentAttributes;

class Reports extends Component
{
    public $playerReports;

    public $autoIncrement = 1;
    
    const CHARACTERS_LIMIT = 300;

    public function initialize()
    {
        $this->playerReports = [];
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::STANDALONE,
            ComponentAttributes::SHARED
        ];
    }
    
    public static function getPlayerId(MineParkPlayer $player) : string
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
    
    public static function isHelper(MineParkPlayer $player) : bool
    {
        return $player->hasPermission(Permissions::ADMINISTRATOR);
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
    
    public function createReport($reportId, MineParkPlayer $reporter, $reportContent)
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

    public function replyReport(MineParkPlayer $replier, $reportId, $content) : bool
    {
        if (!$this->reportExists($reportId)) {
            $replier->sendMessage("ReporterNoTicket");
            return false;
        }

        if (self::symbolsMax($content)) {
            $replier->sendMessage("ReporterMaxChars");
            return false;
        }

        if (self::getHelpers() == null) {
            $replier->sendMessage("ReporterNoAdmins");
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

    private function playerReply(MineParkPlayer $replier, $reportInfo, $reportContent, $reportId)
    {
        $reporter = $reportInfo['reporter'];

        if ($replier->getName() != $reporter->getName()) {
            $replier->sendMessage("ReporterNoAccess");
            return true;
        }

        $replier->sendLocalizedMessage("{ReporterAnswerPart1}$reportId{ReporterAnswerPart2}");
        $replier->sendMessage("§b $reportContent");

        foreach(self::getHelpers() as $helper) {
            $helper->sendLocalizedMessage("{ReporterAnswerPlayerPart1}".$replier->getName()."{ReporterAnswerPlayerPart2}".$reportId."{ReporterAnswerPlayerPart3}");
            $helper->sendMessage("§b $reportContent");
        }
    }
    private function helperReply(MineParkPlayer $replier, $reportInfo, $reportContent, $reportId)
    {
        $reporter = $reportInfo['reporter'];

        if ($replier->getName() == $reporter->getName()) {
            $replier->sendMessage("ReporterSelf");
            return false;
        }
            
        if (!$reporter->isOnline()) {
            $this->closeReport($reportId);
            $replier->sendMessage("ReporterOffline");
            return false;
        }
            
        $reporter->sendLocalizedMessage("{ReporterAnswerHelper1Part1}".$replier->getName()."{ReporterAnswerHelper1Part2}");
        $reporter->sendMessage("§b $reportContent");
        $reporter->sendLocalizedMessage("{ReporterAnswerHelper2Part1}$reportId{ReporterAnswerHelper2Part2}");

        $replier->sendLocalizedMessage("{ReporterAnswerHelper3Part1}$reportId{ReporterAnswerHelper3Part2}".$reporter->getName()."{ReporterAnswerHelper3Part3}");
        $replier->sendMessage("§b $reportContent");

        foreach(self::getHelpers() as $helper) {
                
            if ($helper->getName() == $replier->getName()) {
                continue;
            }

            $helper->sendLocalizedMessage("{ReporterAnswerHelper4Part1}".$replier->getName()."{ReporterAnswerHelper4Part2}".$reportId."{ReporterAnswerHelper4Part3}");
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
                $helper->sendLocalizedMessage("{ReporterTicketClosePart1}$reportId{ReporterTicketClosePart2}");
            }
        }

        if (!$reporter->isOnline()) {
            return true;
        }

        $reporter->sendLocalizedMessage("{ReporterTicketClosePart1}$reportId{ReporterTicketClosePart2}");
        return true;
    }

    public function playerReport(MineParkPlayer $player, $reportContent)
    {
        if (self::getHelpers() == null) {
            $player->sendMessage("ReporterNoHelpers");
            return;
        }
        
        if (self::symbolsMax($reportContent)) {
            $player->sendMessage("ReporterManyWord");
            return;
        }
        
        $this->createNewReport($player, $reportContent);
    }

    private function createNewReport(MineParkPlayer $reporter, $reportContent)
    {
        $reportID = $this->generateReportID();

        $this->createReport($reportID, $reporter, $reportContent);
        
        $reporter->sendLocalizedMessage("{ReporterCreateTicketPart1}$reportID{ReporterCreateTicketPart2}");
        $reporter->sendMessage("§b$reportContent");
        
        $helperName = $this->chooseRandomHelper();

        foreach(self::getHelpers() as $helper) {
            $helper->sendLocalizedMessage("{ReporterNeedHelp1Part1}".$reporter->getName()."{ReporterNeedHelp1Part2}$reportID{ReporterNeedHelp1Part3}");
            $helper->sendMessage("§b$reportContent");
            $helper->sendLocalizedMessage("{ReporterNeedHelp2}$helperName");
        }
    }
}
?>