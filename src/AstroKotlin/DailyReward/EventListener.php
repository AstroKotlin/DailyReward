<?php 

declare(strict_types=1);

namespace AstroKotlin\DailyReward;

use AstroKotlin\DailyReward\commands\RewardCommand;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class EventListener implements Listener {

    public function playerJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $name = $player->getName();

        if(!main::getInstance()->getPlayers()->exists($name)) main::getInstance()->setDayPlayer($name, 1);

        if(main::getInstance()->getPlayers()->get($player->getName())["dd"] == false)
        (new RewardCommand())->sendFormReward($player);
    }
}