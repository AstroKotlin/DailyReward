<?php

declare(strict_types=1);

namespace AstroKotlin\DailyReward\manager;

use AstroKotlin\DailyReward\main;
use muqsit\invmenu\InvMenu;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class EditItemManager {

    public function __construct(private int $day) {}

    public function getDescription(): string {
        return main::getInstance()->getReward()->get((string)"day-".$this->day)["description"];
    }

    public function listItem(): array {
        return main::getInstance()->getReward()->get((string)"day-".$this->day)["items"];
    }

    public function editItem(Player $player): void {
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $menu->setName("Edit item of day " . $this->day);
        $inventory = $menu->getInventory();
        $i = 0;

        foreach($this->listItem() as $item) {
            $i++;
            $inventory->setItem($i, $this->dataToItem($item));
        }

        /** @var Closure $listener */
        $menu->setInventoryCloseListener(function($player, $inventory) {
            $listItem = [];

            for($i = 0; $i < 53; $i++) {
                if(!$inventory->getItem($i)->isNull()) {
                    $listItem[] = $this->itemToData($inventory->getItem($i));
                }
            }

            main::getInstance()->getReward()->setNested("day-" . $this->day . ".items", $listItem);
            main::getInstance()->getReward()->save();

            $player->sendMessage(TextFormat::BOLD . TextFormat::GREEN . "Day " . $this->day . " has been edit!");
        });

        $menu->send($player);
    }

    public function itemToData(Item $item): string {
        $cloneItem = clone $item;
        $itemNBT = $cloneItem->nbtSerialize();
        return base64_encode(serialize($itemNBT));
    }

    public function dataToItem(string $item): Item {
        $itemNBT = unserialize(base64_decode($item));
        return Item::nbtDeserialize($itemNBT);
    }
}