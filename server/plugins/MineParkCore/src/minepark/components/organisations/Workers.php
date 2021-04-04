<?php
namespace minepark\components\organisations;

use minepark\Providers;
use pocketmine\event\Event;
use pocketmine\entity\Effect;

use pocketmine\level\Position;
use pocketmine\entity\EffectInstance;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\base\Component;
use minepark\components\GameChat;
use minepark\defaults\ComponentAttributes;
use minepark\defaults\EventList;
use minepark\defaults\MapConstants;
use minepark\Events;
use minepark\providers\BankingProvider;
use minepark\providers\MapProvider;
use pocketmine\event\block\SignChangeEvent;

class Workers extends Component
{
    public array $words;

    private MapProvider $mapProvider;

    private BankingProvider $bankingProvider;

    private GameChat $gameChat;
    
    public function initialize()
    {
        Events::registerEvent(EventList::SIGN_CHANGE_EVENT, [$this, "sign"]);

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
            "Вазоны","Шаверма","Журнал *Мирный Мир*","Маршрутизаторы","Флешкарты","Спец. корм для Травоядных",
            "Комплектующие для ноутбука","*Play Station 5*","Топовый ПК","Микроскоп","Книжная полка","Рачки"
        ];//90

        $this->mapProvider = Providers::getMapProvider();

        $this->bankingProvider = Providers::getBankingProvider();

        $this->gameChat = Components::getComponent(GameChat::class);
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::STANDALONE
        ];
    }

    public function sign(SignChangeEvent $event)
    {
        $player = $event->getPlayer();
        $lns = $event->getLines();

        if ($lns[0] == "[workers1]" and $player->isOp()) {
            $this->handleWorker1($event);
        } elseif ($lns[0] == "[workers2]" and $player->isOp()) {
            $this->handleWorker2($event);
        }
    }
    
    private function handleWorker1(SignChangeEvent $event)
    {
        $event->setLine(0, "§eЗдесь можно"); 
        $event->setLine(1, "§eподзаработать");
        $event->setLine(2, "§f(грузчики)");
        $event->setLine(3, "§b/takebox");
    }
    
    private function handleWorker2(SignChangeEvent $event)
    {
        $event->setLine(0, "§aЗдесь находится"); 
        $event->setLine(1, "§aточка разгрузки");
        $event->setLine(2, "§f(грузчики)");
        $event->setLine(3, "§6Разгрузиться: §b/putbox");
    }

    public function ifPointIsNearPlayer(Position $pos, int $group)
    {
        $points = $this->mapProvider->getNearPoints($pos, 6);

        foreach($points as $point) {
            if($this->mapProvider->getPointGroup($point) == $group) {
                return true;
            }
        }

        return false;
    }

    public function takebox(MineParkPlayer $player)
    {
        $hasPoint = $this->ifPointIsNearPlayer($player->getPosition(), MapConstants::POINT_GROUP_WORK1);

        if(!$hasPoint) {
            $player->sendMessage("§cРядом нет площадки с ящиками!");
            return;
        }

        if($player->getStatesMap()->loadWeight == null) {
            $this->handleBoxTake($player);
            return;
        }

        $player->sendMessage("§cСначала положите ящик из ваших рук на склад!");
    }
    
    private function handleBoxTake(MineParkPlayer $player)
    {
        $player->addEffect(new EffectInstance(Effect::getEffect(2), 20 * 9999, 3));

        $box = $this->words[mt_rand(0, count($this->words))]; 
        $player->getStatesMap()->loadWeight = mt_rand(1, 12); 
        
        $player->sendMessage("§7Найдите точку разгрузки и положите ящик!");
        
        $this->gameChat->sendLocalMessage($player, "§8(§dв руках ящик с надписью | $box |§8)", "§d : ", 12);
    
        $player->getStatesMap()->bar = "§aВ руках ящик около " . $player->getStatesMap()->loadWeight . " кг";
    }

    public function putbox(MineParkPlayer $player)
    {
        $hasPoint = $this->ifPointIsNearPlayer($player->getPosition(), MapConstants::POINT_GROUP_WORK2);

        if(!$hasPoint) {
            $player->sendMessage("§cРядом нет точек для разрузки!");
            return;
        }

        if($player->getStatesMap()->loadWeight != null) {
            $this->handlePutBox($player);
            return;
        }

        $player->sendMessage("§cВам необходимо взять ящик со склада!");
    }
    
    private function handlePutBox(MineParkPlayer $player)
    {
        $player->removeAllEffects();

        $this->gameChat->sendLocalMessage($player, "§8(§dЯщик расположился на складе§8)", "§d : ", 12);
        $this->bankingProvider->givePlayerMoney($player, 20 * $player->getStatesMap()->loadWeight);

        $player->getStatesMap()->loadWeight = null; 
        $player->getStatesMap()->bar = null;
    }
}
?>