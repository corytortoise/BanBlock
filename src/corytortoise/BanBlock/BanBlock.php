<?php

namespace corytortoise\BanBlock;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\utils\TextFormat as C;

class BanBlock extends PluginBase implements Listener {

  public function onEnable() {
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    @\mkdir($this->getDataFolder());
    $this->saveDefaultConfig();
    $this->getLogger()->notice(\count(\array_keys($this->parseBlocks())) . " Blocks have been banned.");
  }

  public function parseBlocks(array $blocks = []) {
    if(empty($blocks)) {
      $blocks = $this->getConfig()->get("blocks");
    }
    $result = [];
    foreach($blocks as $block) {
      if(\strpos($block, ":") === false) {
        $result[$block] = true;
        continue;
      } else {
        $data = \explode(":", $block);
        $result[$data[1]] = $data[1];
        continue;
      }
    }
    return $result;
  }

  /**
   *
   * @param \corytortoise\BanBlock\Player $player
   */
  private function handleMessage(Player $player) {
    if($this->getConfig()->get("mute") === false) {
      $player->sendMessage(C::RED . $this->getConfig()->get("message"));
    }
  }

  /**
   * @priority HIGHEST
   * @ignoreCancelled true
   * @param BlockBreakEvent $event
   */
  public function onBreak(BlockBreakEvent $event) {
    $block = $event->getBlock()->getId();
    if(\in_array($block, $data = $this->parseBlocks())) {
      $player = $event->getPlayer();
      if(!$player->hasPermission("banblock") && !$player->isOp()) {
        if($data[$block] === true) {
          $event->setCancelled(true);
          $this->handleMessage($player);
        } else {
          if($event->getBlock()->getMeta() === $data[$block]) {
            $event->setCancelled(true);
            $this->handleMessage($player);
          }
        }
      }
    }
  }

}

