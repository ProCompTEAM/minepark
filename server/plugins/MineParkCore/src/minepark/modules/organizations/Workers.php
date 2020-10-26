<?php
namespace minepark\modules\organizations;

use pocketmine\Player;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Effect;
use pocketmine\level\Position;

use minepark\Core;

class Workers
{
    public $words;
    
    const TAKEBOX_POINT = 5;
    const PUTBOX_POINT = 6;
	
	public function __construct()
	{
		$this->words = [
			"Сельдь *Московская*","Картофель *Беларус*","Боярышник","*Contex Classic*",
            "*Contex с пупырышками*","Шоколад *Алёнка*","Трубы водопроводные","Пойманные морские обитатели",
            "Сало","Коньяк SHABO","Кактус декоративный","Стекло","Гельдуш *Аноним*","Падаль","Очищенная питьевая вода",
            "Морковь","Вода Бонаква","Сигареты *Prima*","Диски *WannaCry*","Спец. корм для Летающих особей",
            "Складной велосипед","Плюшевые мишки","Куклы *БАРБИ*","Земляника","Колбаса","Обои","Сыр","Пиво",
            "Шлакоблоки молотые","Ракушки","Тушки лосося","Известь","Стеклопакет","Морские водоросли","Семечки *Гоп*",
            "Хлеб *Ладушки*","Булочки *Повариха*","Морские свинки","Одежда", "Патифоны","Матрас *Моряк*","Спиннеры",
            "Питьевая вода","Порох","Кирпич","Пластик","Песок","Сельдерей","Творог *Коровка*","Йогурты *Данон*", 
            "Синтезатор *Casio*","Скрипка *Страдивари*","Лопаты", "Железная руда","Пищевая соя","Творог *Ростишка*",
            "Гречка *Мир*","Хотдоги","Масло","Туалетная бумага *Нежность*","Игрушечное яйцо динозавра","Рис","Перец чили",
            "Макароны","Торт *Наполеон*","Яблоки","Респераторы","Бумага","Школьный мел","Сок из оливок","Лимонный сок",
            "Сок из кактусов","Ликер","Коньяк","Специи","Журналы *Черепашки Ниньдзя*","Глина","Учебник по алгебре",
            "Вазоны","Шаверма","Журнал *Всё о динозаврах*","Маршрутизаторы","Флешкарты","Спец. корм для Травоядных",
            "Комплектующие к ПК","*Play Station*","Топовый ПК","Микроскоп","Книжная полка","Спец. корм для Хищников"
        ];//90
	}
    
    protected function getCore() : Core
    {
        return Core::getActive();
    }

	public function sign($event)
	{
		$player = $event->getPlayer();
        $lns = $event->getLines();

		if ($lns[0] == "[workers1]" and $player->isOp()) {
			$this->handleWorker1($event);
		} elseif ($lns[0] == "[workers2]" and $player->isOp()) {
			$this->handleWorker2($event);
		}
    }
    
    private function handleWorker1($event)
    {
        $event->setLine(0, "§eЗдесь можно"); 
		$event->setLine(1, "§eподзаработать");
		$event->setLine(2, "§f(грузчики)");
		$event->setLine(3, "§b/takebox");
    }
    
    private function handleWorker2($event)
    {
        $event->setLine(0, "§aЗдесь находится"); 
		$event->setLine(1, "§aточка разгрузки");
		$event->setLine(2, "§f(грузчики)");
		$event->setLine(3, "§6Разгрузиться: §b/putbox");
    }

    public function ifPointIsNearPlayer(Position $pos, int $point)
    {
        $points = $this->getCore()->getMapper()->getNearPoints($pos, 5);

		foreach($points as $point) {
			if($this->core->getMapper()->getPointGroup($point) == $point) {
                return true;
            }
        }

        return false;
    }

	public function takebox(Player $p)
	{
        $hasPoint = $this->ifPointIsNearPlayer($p->getPosition(), self::TAKEBOX_POINT);

		if(!$hasPoint) {
            $p->sendMessage("§cРядом нет площадки с ящиками!");
            return;
        }

        if($p->wbox == null) {
            $this->handleBoxTake($p);
            return;
        }

        $p->sendMessage("§cСначала положите ящик из ваших рук на склад!");
	}
    
    private function handleBoxTake(Player $player)
    {
        $player->addEffect(new EffectInstance(Effect::getEffect(2), 20 * 9999, 3));

		$box = $this->words[mt_rand(0, count($this->words))]; 
        $player->wbox = mt_rand(1, 12); 
        
        $player->sendMessage("§7Найдите точку разгрузки и положите ящик!");
        
        $this->getCore()->getChatter()->send($player, "§8(§dв руках ящик с надписью | $box |§8)", "§d : ", 12);
    
		$player->bar = "§aВ руках ящик около " . $player->wbox . " кг";
    }

	public function putbox(Player $player)
	{
        $hasPoint = $this->ifPointIsNearPlayer($player->getPosition(), self::PUTBOX_POINT);

		if(!$hasPoint) {
            $player->sendMessage("§cРядом нет точек для разрузки!");
            return;
        }

        if($player->wbox != null) {
            $this->handlePutBox($player);
            return;
        }

        $player->sendMessage("§cВам необходимо взять ящик со склада!");
    }
    
    private function handlePutBox(Player $player)
    {
        $player->removeAllEffects();

        $this->getCore()->getChatter()->send($player, "§8(§dЯщик расположился на складе§8)", "§d : ", 12);
        $this->getCore()->getBank()->givePlayerMoney($player, 20 * $player->wbox);

        $player->wbox = null; 
        $player->bar = null;
    }
}
?>