<?php


namespace JviguyGamesYT\DarkPvPCore\BossBar;

use InvalidArgumentException;
use JviguyGamesYT\DarkPvPCore\FFAs\Player;
use JviguyGamesYT\DarkPvPCore\Main;
use JviguyGamesYT\DarkPvPCore\Spawn\SpawnTask;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\Server;

class BossBarListener implements Listener
{
    private $main;
    public function __construct(Main $main){
        $this->main = $main;
    }
    public function onDataPacketReceiveEvent(DataPacketReceiveEvent $e)
    {
        if ($e->getPacket() instanceof BossEventPacket) $this->onBossEventPacket($e);
    }

    private function onBossEventPacket(DataPacketReceiveEvent $e)
    {
        if (!($pk = $e->getPacket()) instanceof BossEventPacket) throw new InvalidArgumentException(get_class($e->getPacket()) . " is not a " . BossEventPacket::class);
        /** @var BossEventPacket $pk */
        switch ($pk->eventType) {
            case BossEventPacket::TYPE_REGISTER_PLAYER:
            case BossEventPacket::TYPE_UNREGISTER_PLAYER:
                Server::getInstance()->getLogger()->debug("Got BossEventPacket " . ($pk->eventType === BossEventPacket::TYPE_REGISTER_PLAYER ? "" : "un") . "register by client for player id " . $pk->playerEid);
                break;
            default:
                $e->getPlayer()->kick("Invalid packet received", false);
        }
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        if ($player instanceof Player) {
            $player->setBar((new DiverseBossBar())->addPlayer($player)->setPercentage(1));
            $this->main->getScheduler()->scheduleRepeatingTask(new SpawnTask($player), 10);
        }
    }
    public function onPlayerCreation(PlayerCreationEvent $event) {
    $event->setPlayerClass(Player::class);
    }
}