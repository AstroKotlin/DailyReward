<?php 

declare(strict_types=1);

namespace AstroKotlin\DailyReward\commands;

use AstroKotlin\DailyReward\main;
use AstroKotlin\DailyReward\manager\EditItemManager;
use AstroKotlin\DailyReward\ModalForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;

class RewardCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("daily", "điểm danh hàng ngày ở đây!", null, ["diemdanh", "dailyreward"]);
        $this->setPermission("dailyreward.rw.cmd");
    }

    public function getOwningPlugin(): Plugin {
        return main::getInstance();
    }

    public function execute(CommandSender $player, string $commandLabel, array $args): bool {
        if(!$player instanceof Player) {
            $player->sendMessage(TextFormat::RED . 'Please use in game!');
            return false;
        }

        $this->sendFormReward($player);

        return true;
    }

    public function sendFormReward(Player $player) {
        if(!main::getInstance()->getReward()->exists((string)"day-".main::getInstance()->getDayPlayer($player->getName()))) return;
        
        $itemsName = "";

        foreach(main::getInstance()->getReward()->get((string)"day-".main::getInstance()->getDayPlayer($player->getName()))["items"] as $itemData) {
            $itemsName .= TextFormat::WHITE . "- " . $this->dataToItem($itemData)->getName()."\n";
        }

        if($itemsName == "") $itemsName = TextFormat::WHITE . "Không có vật phẩm!";

        $player->sendForm(new ModalForm(TextFormat::BLACK . TextFormat::BOLD . "Bạn có muốn nhận quà điểm danh ngày hôm nay không?", TextFormat::GREEN . TextFormat::BOLD . "Quà điểm danh ngày " . main::getInstance()->getDayPlayer($player->getName()) . "\n" . TextFormat::BLUE . "Thư mô tả: " . (new EditItemManager(main::getInstance()->getDayPlayer($player->getName())))->getDescription() . "\n" . TextFormat::AQUA . "Phần thưởng:\n" . $itemsName, TextFormat::GREEN . TextFormat::BOLD . "Đồng ý", TextFormat::RED . TextFormat::BOLD . "Không", function(Player $player, $data) {
            if($data == null or $data == false) return;

            if($data) {
                $itemsData = (new EditItemManager(main::getInstance()->getDayPlayer($player->getName())))->listItem();

                foreach($itemsData as $itemData) {
                    $player->getInventory()->addItem($this->dataToItem($itemData));
                }

                main::getInstance()->dd($player->getName());

                $player->sendMessage(TextFormat::GREEN . TextFormat::BOLD . "Bạn đã nhận thành công phần quà điểm danh ngày hôm nay!");
            }
        }));
    }

    public function dataToItem(string $item): Item {
        $itemNBT = unserialize(base64_decode($item));
        return Item::nbtDeserialize($itemNBT);
    }
}