<?php 

declare(strict_types=1);

namespace AstroKotlin\DailyReward\commands;

use AstroKotlin\DailyReward\CustomForm;
use AstroKotlin\DailyReward\main;
use AstroKotlin\DailyReward\manager\EditItemManager;
use AstroKotlin\DailyReward\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;

class EditRewardCommand extends Command implements PluginOwned {

    public function getOwningPlugin(): Plugin {
        return main::getInstance();
    }

    public function __construct() {
        parent::__construct("editdaily", "chinh sua điểm danh hàng ngày ở đây!", null, ["editdiemdanh", "editreward"]);
        $this->setPermission("dailyreward.edit.cmd");
    }

    public function execute(CommandSender $player, string $commandLabel, array $args): bool {
        if(!$player instanceof Player) {
            $player->sendMessage(TextFormat::RED . 'Please use in game!');
            return false;
        }

        $this->sendEditGui($player);
        return true;
    }

    public function sendEditGui(Player $player): void {
        $form = new SimpleForm("Edit Daily Reward ", "", function(Player $player, $data) {
            if($data == null or $data == 0) return;

            if($data == 1) {
                $this->sendAddForm($player);
            }
        });
        $form->addButton("Exit");
        $form->addButton("add daily reward");
        $form->addButton("edit daily reward");
        $player->sendForm($form);
    }

    public function sendSelectEditForm(Player $player): void {
        
    }

    public function sendAddForm(Player $player): void {
        $form = new CustomForm("Add Reward", function(Player $player, $data) {
            if($data == null) return;
            
            if(!isset($data[0]) and !isset($data[1])) {
                $player->sendMessage(TextFormat::RED . TextFormat::BOLD . "Pls input day and description!");
            }

            if(!is_numeric($data[0])) {
                $player->sendMessage(TextFormat::RED . TextFormat::BOLD . "Pls input number!");
                return;
            }

            if(!(int)$data[0] > 0 and !(int)$data[0] <= 31) {
                $player->sendMessage(TextFormat::RED . TextFormat::BOLD . "Pls input day > 0 and <= 31!");
                return;
            }

            if(main::getInstance()->getReward()->exists((string)"day-" . $data[0])) {
                $player->sendMessage(TextFormat::RED . TextFormat::BOLD . "Day has already exists!");
                return;
            }

            main::getInstance()->getReward()->set((string)"day-" . $data[0], ["description" => $data[1], "items" => []]);
            main::getInstance()->getReward()->save();

            $mng = new EditItemManager((int)$data[0]);

            $mng->editItem($player);
        });
        $form->addInput("day", "Example: 1", "1");
        $form->addInput("description", "CU anh Bao dai 10cm a");
        $player->sendForm($form);
    }
}