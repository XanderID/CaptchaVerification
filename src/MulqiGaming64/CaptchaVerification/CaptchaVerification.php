<?php

namespace MulqiGaming64\CaptchaVerification;

use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

use pocketmine\utils\Config;
use pocketmine\scheduler\ClosureTask;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

use czechpmdevs\imageonmap\ImageOnMap;
use czechpmdevs\imageonmap\item\FilledMap;
use GdImage;

class CaptchaVerification extends PluginBase implements Listener {
	
	 /** @var Config $session */
    private $session;
    /** @return array */
    private $sessionTime = [];
    
    /** @var Config $joined */
    private $joined;
    
    /** @return array */
    private $captcha = [];
    /** @return array */
    private $inventoryPlayer = [];
    /** @return array */
    private $resolved = [];
    /** @return array */
    private $attempt = [];
	
    public function onEnable(): void{
    	$this->saveDefaultConfig();
   	 if (!extension_loaded("gd")) {
            $this->getServer()->getLogger()->info("Please install ImageGD php Extension");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }
        if (!file_exists($this->getDataFolder() . "captcha")) {
        	mkdir($this->getDataFolder() . "captcha", 0777);
        }
        $this->session = new Config($this->getDataFolder() . "session.yml", Config::YAML, array());
    	$this->sessionTime = $this->session->getAll();
    	if($this->getConfig()->get("first-join")){
    		$this->joined = new Config($this->getDataFolder() . "joined.yml", Config::YAML, array());
		}
    	$this->getScheduler()->scheduleDelayedRepeatingTask(new ClosureTask(
        	function(){
            	$this->session->setAll($this->sessionTime);
				$this->session->save();
            }
        ), 20 * 60, 20 * 60);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function onDisable(): void{
    	// Save all Player session
    	$this->session->setAll($this->sessionTime);
		$this->session->save();
	}
	
	/**
	* @param GdImage $img
	* @param string $color
	*/
	public static function getColor(GdImage $img, string $color): int{
		switch($color){
			case "BLACK":
			case "§0":
			case "&0":
				return @imagecolorallocate($img, 29, 28, 33);
			break;
			case "DARK_BLUE":
			case "§1":
			case "&1":
				return @imagecolorallocate($img, 60, 68, 169);
			break;
			case "DARK_GREEN":
			case "§2":
			case "&2,":
				return @imagecolorallocate($img, 93, 124, 21);
			break;
			case "DARK_AQUA":
			case "§3":
			case "&3":
				return @imagecolorallocate($img, 22, 156, 157);
			break;
			case "DARK_RED":
			case "§4":
			case "&4":
				return @imagecolorallocate($img, 176, 46, 38);
			break;
			case "DARK_PURPLE":
			case "§5":
			case "&5":
				return @imagecolorallocate($img, 137, 50, 183);
			break;
			case "GOLD":
			case "§6":
			case "&6":
				return @imagecolorallocate($img, 249, 128, 29);
			break;
			case "GRAY":
			case "§7":
			case "&7":
				return @imagecolorallocate($img, 156, 157, 151);
			break;
			case "DARK_GRAY":
			case "§8":
			case "&8":
				return @imagecolorallocate($img, 71, 79, 82);
			break;
			case "BLUE":
			case "§9":
			case "&9":
				return @imagecolorallocate($img, 60, 68, 169);
			break;
			case "GREEN":
			case "§a":
			case "&a":
				return @imagecolorallocate($img, 128, 199, 31);
			break;
			case "AQUA":
			case "§b":
			case "&b":
				return @imagecolorallocate($img, 58, 179, 218);
			break;
			case "RED":
			case "§c":
			case "&c":
				return @imagecolorallocate($img, 255, 83, 73);
			break;
			case "LIGHT_PURPLE":
			case "§d":
			case "&d":
				return @imagecolorallocate($img, 255, 0, 255);
			break;
			case "YELLOW":
			case "§e":
			case "&e":
				return @imagecolorallocate($img, 255, 255, 0);
			break;
			case "WHITE":
			case "§f":
			case "&f":
				return @imagecolorallocate($img, 249, 255, 255);
			break;
			default:
				return @imagecolorallocate($img, 249, 255, 255);
			break;
		}
	}
	
	/**
	* @param Player $player
	* @param bool $regenerate
	*/
	public function sendCode(Player $player, $regenerate = false): void{
		$player->getInventory()->clearAll();
		$name = strtolower($player->getName());
		$this->resolved[$name] = false; // For cancel in event
		$this->attempt[$name] = 0; // For attempt
		$file = $this->getDataFolder() . "captcha/" . $name . ".png"; // For save to file
		if($this->getConfig()->get("number")){
			$words = "0123456789AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz"; // this is just raw to be random
		} else {
			$words = "AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz"; // // this is just raw to be random
		}
		$wordslength = strlen($words);
		$captcha = ""; // Randomed Words
        for ($i = 0; $i < 5; $i++) {
          $captcha .= $words[rand(0, $wordslength - 1)]; // random words
        }

		// Image for MAP
		$img = @imagecreate(60, 60); // Create base image
		self::getColor($img, $this->getConfig()->get("background-color")); // set background color
        @imagestring($img, 5, 9, 22, $captcha, self::getColor($img, $this->getConfig()->get("text-color"))); // add text
        
        // Resize
        $img2 = @imagecreate(128, 128); // Create base Image
        @imagecopyresampled($img2, $img, 0, 0, 0, 0, 128, 128, 60, 60); // why copy from $img?, instead of just direct it? because it uses an imagestring font size max 5
        
        @imagepng($img2, $file); // Save to File
		@imagedestroy($img2); // clear cache
		@imagedestroy($img); // clear cache
		
		$api = ImageOnMap::getInstance();
		$mapid = $api->getImageFromFile($file, 1, 1, 0, 0);
		
		/** @var FilledMap $map */
		$map = (FilledMap::get())->setMapId($mapid);
		$this->captcha[$name] = $captcha;
		
		// why not directly set the item? because i have tried and my minecraft crashes
		if($regenerate){
			$player->sendMessage($this->getConfig()->get("regenerate"));
		} else {
			$player->sendMessage("§aGenerating Captcha Code, please wait 3 seconds");
		}
		$player->getInventory()->setHeldItemIndex(1);
        $player->getInventory()->setItem(0, $map);
        $this->getScheduler()->scheduleDelayedTask(new ClosureTask(
        	function() use($player){
            	$player->getInventory()->setHeldItemIndex(0);
     			$player->sendMessage($this->getConfig()->get("input-code"));
            }
        ), 60);
	}
	
	/**
	* @param PlayerJoinEvent $event
	* @priority HIGHEST
	*/
	public function onJoin(PlayerJoinEvent $event): void{
		$player = $event->getPlayer();
		$name = strtolower($player->getName());
		$this->inventoryPlayer[$name] = $player->getInventory()->getContents(); // Saving
			
		if($this->getConfig()->get("first-join")){
			if(!$this->joined->get($name)){
				$this->sendCode($player);
				$this->joined->set($name, true);
			}
		} else {
			if(isset($this->sessionTime[$name])){
				if(time() > $this->sessionTime[$name]){
					$this->sendCode($player);
				}
			} else {
				$this->sendCode($player);
			}
		}
	}
	
	/**
	* @priority HIGHEST
	* @param PlayerChatEvent $event
	*/
	public function onChat(PlayerChatEvent $event): bool{
		$player = $event->getPlayer();
		$name = strtolower($player->getName());
		$msg = $event->getMessage();
		if(isset($this->captcha[$name])){
			$event->setRecipients([$player]);
			// check attempt
			if($this->attempt[$name] >= ($this->getConfig()->get("max-attempt") - 1)){
				$player->sendMessage($this->getConfig()->get("wrong-code"));
				switch($this->getConfig()->get("mode")){
					case "kick":
						unset($this->captcha[$name]);
						$player->kick($this->getConfig()->get("kick"));
						return true;
					break;
					case "regenerate":
						unset($this->captcha[$name]);
						$this->sendCode($player, true);
						return true;
					break;
					default:
						unset($this->captcha[$name]);
						$this->sendCode($player, true);
						return true;
					break;
				}
				return true;
			}
			if($msg !== $this->captcha[$name]){
				$player->sendMessage($this->getConfig()->get("wrong-code"));
				$this->attempt[$name] += 1;
				return false;
			}
			$player->sendMessage($this->getConfig()->get("correct-code"));
			$player->getInventory()->setContents($this->inventoryPlayer[$name]); // Give Inventory back to player
			$this->resolved[$name] = true;
			unset($this->captcha[$name]);
			unset($this->attempt[$name]);
			$this->sessionTime[$name] = time() + $this->getConfig()->get("session");
			return true;
		}
	}
	
	/**
	* @param PlayerMoveEvent $event
	* @priority HIGHEST
	*/
	public function onMove(PlayerMoveEvent $event): void{
		$player = $event->getPlayer();
		$name = strtolower($player->getName());
		$from = $event->getFrom();
		$to = $event->getTo();
		if($this->getConfig()->get("cancel-move")){
			if(isset($this->resolved[$name])){
				if(!$this->resolved[$name]){
					if($to->getX() !== $from->getX() or $to->getY() !== $from->getY() or $to->getZ() !== $from->getZ()){
						$event->setTo($from); // Alternate $event->cancel
						$player->sendTip($this->getConfig()->get("tip-message"));
					}
				}
			}
		}
	}
	
	/**
	* @param PlayerItemHeldEvent $event
	* @priority HIGHEST
	*/
	public function onHeld(PlayerItemHeldEvent $event): void{
		$player = $event->getPlayer();
		$name = strtolower($player->getName());
		if(isset($this->resolved[$name])){
			if(!$this->resolved[$name]){
				$event->cancel(); // Cancel, for fix minecraft crash
			}
		}
	}
	
	/**
	* @param PlayerDropItemEvent $event
	* @priority HIGHEST
	*/
	public function onDrop(PlayerDropItemEvent $event): void{
		$player = $event->getPlayer();
		$name = strtolower($player->getName());
		if(isset($this->resolved[$name])){
			if(!$this->resolved[$name]){
				$event->cancel(); // Cancel
			}
		}
	}
	
	/**
	* @param PlayerInteractEvent $event
	* @priority HIGHEST
	*/
	public function onInteract(PlayerInteractEvent $event): void{
		$player = $event->getPlayer();
		$name = strtolower($player->getName());
		// cancel captcha placing
		if(isset($this->resolved[$name])){
			if(!$this->resolved[$name]){
				$event->cancel(); // Cancel
			}
		}
	}
	
	/**
	* @param InventoryTransactionEvent $event
	* @priority HIGHEST
	*/
	public function onTransaction(InventoryTransactionEvent $event): void{
		$player = $event->getTransaction()->getSource();
		$name = strtolower($player->getName());
		if(isset($this->resolved[$name])){
			if(!$this->resolved[$name]){
				$event->cancel(); // Cancel
			}
		}
	}
	
	/**
	* @param PlayerCommandPreprocessEvent $event
	* @priority HIGHEST
	*/
	public function onPreprocess(PlayerCommandPreprocessEvent $event): void{
		$player = $event->getPlayer();
		$name = strtolower($player->getName());
		$cmd = substr($event->getMessage(), 0, 1);
		if(!$this->getConfig()->get("can-command") && $cmd == "/"){
			if(isset($this->resolved[$name])){
				if(!$this->resolved[$name]){
					$event->cancel(); // Cancel,
					$player->sendMessage($this->getConfig()->get("cmd-message"));
				}
			}
		}
	}
}
