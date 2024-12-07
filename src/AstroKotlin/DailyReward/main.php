<?php

declare(strict_types=1);

namespace AstroKotlin\DailyReward;

use AstroKotlin\DailyReward\commands\EditRewardCommand;
use AstroKotlin\DailyReward\commands\RewardCommand;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use vennv\vapm\System;
use vennv\vapm\VapmPMMP;

class main extends PluginBase {

    public Config $reward, $players;

    public array $time = ["gio" => 0, "phut" => 0, "giay" => 0];

    use SingletonTrait;

    protected function onLoad(): void {
        self::setInstance($this);
    }

    protected function onEnable(): void {
        $this->getLogger()->notice(TextFormat::GREEN . "Daily reward has on enable!");
        $this->getServer()->getPluginManager()->registerEvents(new EventListener, $this);

        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $this->players = new Config($this->getDataFolder() . "players.yml", Config::YAML);
        $this->reward = new Config($this->getDataFolder() . "reward.yml", Config::YAML);

        $this->getServer()->getCommandMap()->register("daily", new RewardCommand());
        $this->getServer()->getCommandMap()->register("editdaily", new EditRewardCommand());


        if(!InvMenuHandler::isRegistered()){
            InvMenuHandler::register($this);
        }

        VapmPMMP::init($this);

        $gio = date('H');
        $phut = date('i');
        $giay = date('s');
        
        $this->setTime((int)$gio, (int)$phut, (int)$giay);

        System::setInterval(function(): void {
            foreach($this->getServer()->getOnlinePlayers() as $player) {
                $player->sendPopup(TextFormat::GREEN . "".$this->time["gio"]." giờ, ".$this->time["phut"]." phút, ".$this->time["giay"]." giây");
            }
            $this->addTime(0, 0, 1);

            if($this->time['giay'] >= 60) {
                $this->time['phut']++;
                $this->time['giay'] = 0;
            }

            if($this->time['phut'] >= 60) {
                $this->time['gio']++;
                $this->time['phut'] = 0;
            }

            if($this->time['gio'] >= 24) {
                $this->setTime(0, 0, 0);
                foreach($this->getPlayers()->getAll() as $player => $data) {
                    $this->addDayPlayer((string)$player, 1);

                    if($this->getDayPlayer((string)$player) > 31) $this->setDayPlayer((string)$player, 1);
                }
            }
        },1000);
    }

    public function setTime(int $gio, int $phut, int $giay): void {
        $this->time = ["gio" => $gio, "phut" => $phut, "giay" => $giay];
    }

    public function addTime(int $gio, int $phut, int $giay): void {
        $this->time = ["gio" => $this->time["gio"] + $gio, "phut" => $this->time["phut"] + $phut, "giay" => $this->time["giay"] + $giay];
    }

    protected function onDisable(): void {}

    public function getReward(): Config {
        return $this->reward;
    }

    public function getPlayers(): Config {
        return $this->players;
    }

    public function setDayPlayer(string $player, int $day, bool $dd = false): void {
        $this->getPlayers()->set($player, ["day" => $day, "dd" => $dd]);
        $this->getPlayers()->save();
    }
    
    public function addDayPlayer(string $player, int $day): void {
        $this->getPlayers()->setNested($player . ".day", $day + $this->getPlayers()->get($player)["day"]);
        $this->getPlayers()->setNested($player . ".dd", false);
        $this->getPlayers()->save();
    }

    public function dd(string $player): void {
        $this->getPlayers()->setNested($player . ".dd", true);
        $this->getPlayers()->save();
    }

    public function getDayPlayer(string $player): int {
        return $this->getPlayers()->get($player)["day"];
    }
}