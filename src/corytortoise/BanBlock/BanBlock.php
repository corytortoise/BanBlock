<?php

namespace corytortoise\BanBlock;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;
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
        $result[$data[0]] = $data[1];
        continue;
      }
    }
    return $result;
  }

  /**
   * Sends a player a message from Config.
   * @param Player $player
   */
  private function handleMessage(Player $player) {
    if($this->getConfig()->get("mute") === false) {
      $player->sendMessage(C::RED . $this->getConfig()->get("message"));
    }
  }

  /**
   * Returns whether a player is allowed to break the block or not.
   * @param Player $player
   * @return boolean
   */
  public function canBreak(Player $player) {
    if($this->getConfig()->get("perm") && $player->hasPermission("banblock.break")) {
      return true;
    }
    switch($this->getConfig()->get("type")) {
      case "whitelist":
      case "wl":
        if(in_array($player->getName(), $this->getConfig()->get("players"))) {
          return true;
        }
        return false;
      case "blacklist":
      case "bl":
        if(in_array($player->getName(), $this->getConfig()->get("players"))) {
          return false;
        }
        return true;
      case "null":
        return false;
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
      if(!$this->canBreak($player)) {
        if($data[$block] === true) {
          $event->setCancelled(true);
          $this->handleMessage($player);
        } else {
          if($event->getBlock()->getDamage() == $data[$block]) {
            $event->setCancelled(true);
            $this->handleMessage($player);
          }
        }
      }
    }
  }

}

