<?php
namespace EconomyAPI;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class EconomyAPI extends PluginBase implements Listener
{
    private $money;
    private $payCooldown = array();
    private $cooldowns;
    private static $instance;
    private $messages = [
        'prefix' => '§l§cMONEY MANAGER:§r ',
        'addmoney.usage' => '&cUsage: /addmoney <amount> <player>',
        'addmoney.invalid' => '&cInvalid amount!',
        'addmoney.no_player' => '&cNo matching online player!',
        'addmoney.success' => '&aAdded &e{%0} Coins &ato &b{%1}',
        'takemoney.usage' => '&cUsage: /takemoney <player> amount:<amount|full>',
        'takemoney.no_player' => '&cNo matching online player!',
        'takemoney.invalid' => '&cInvalid amount!',
        'takemoney.success' => '&aTook &e{%0} Coins &afrom &b{%1}',
        'removemoney.usage' => '&cUsage: /removemoney <player> amount:<amount|full>',
        'removemoney.no_player' => '&cNo matching online player!',
        'removemoney.invalid' => '&cInvalid amount!',
        'removemoney.success' => '&cRemoved &e{%0} Coins &cfrom &b{%1}',
        'pay.usage' => '&cUsage: /pay <amount> <player>',
        'pay.invalid' => '&cInvalid amount!',
        'pay.no_player' => '&cPlayer must be online.',
        'pay.self' => '&cYou cannot pay yourself!',
        'pay.insufficient' => '&cYou don\'t have enough money!',
        'pay.cooldown' => '&cWait {%0}s before next payment!',
        'pay.received_tip' => '&a+{%0} Coins',
        'pay.broadcast' => '&b{%0} &fpaid &a{%1} Coins &fto &b{%2}',
        'pay.success' => '&aPayment successful!',
        'paytime.can_pay_now' => '&aYou can pay now!',
        'paytime.wait' => '&eYou can pay in after {%0}s',
        'money.balance' => '&fYou have &a{%0} Coins',
        'seemoney.balance' => '&b{%0} &fhas &a{%1} Coins',
    ];
    public function onEnable()
    {
        self::$instance = $this;
        @mkdir($this->getDataFolder());
        if(!file_exists($this->getDataFolder()."money.yml"))
            {
            $this->saveResource("money.yml");
        }
        if(!file_exists($this->getDataFolder()."cooldowns.yml"))
            {
            $this->saveResource("cooldowns.yml");
        }
        $this->cooldowns = new Config($this->getDataFolder()."cooldowns.yml",Config::YAML);
        $this->money = new Config($this->getDataFolder()."money.yml",Config::YAML);
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
    }
    public function trans($target, $key,array $params = [])
    {
        $text = isset($this->messages[$key]) ? $this->messages[$key]: $key;
        foreach($params as $i => $p)
            {
            $text = str_replace("{%". $i ."}",
            (string) $p,$text);
        }
        return str_replace("&", "\xc2\xa7", $text);
    }
    public function isAdministratorOfVeoZaxAPI(CommandSender $sender)
    {
        return $sender->isOp();
    }
    private function msg($target, $key,array $params = [])
    {
        return $this->trans($target, "prefix").$this->trans($target, $key, $params);
    }
    public static function getInstance()
    {
        return self::$instance;
    }
    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        if(!$this->money->exists($name))
            {
            $this->money->set($name, 0);
            $this->money->save();
        }
    }
    private function findPlayer($input)
    {
        $input = strtolower($input);
        $players = $this->getServer()->getOnlinePlayers();
        $matches = array();
        foreach($players as $player)
            {
            $name = strtolower($player->getName());
            if(strpos($name, $input) === 0)
                {
                $matches[] = $player;
            }
        }
        if(empty($matches))
            {
            return null;
        }
        usort($matches,function($a, $b)
        {
            return
            strcmp(strtolower($a->getName()),
            strtolower($b->getName()));
        });
        return $matches[0];
    }
    public function getMoney($player)
    {
        return
        $this->myMoney($player);
    }
    public function myMoney($player)
    {
        $name = strtolower($player instanceof Player ? $player->getName(): $player);
        if(!$this->money->exists($name))
            {
            return 0;
        }
        return
        (int)$this->money->get($name);
    }
    public function addMoney($player, $amount)
    {
        if($amount <= 0)
            return false;
        $name = strtolower($player instanceof Player ? $player->getName(): $player);
        $current = $this->money->get($name, 0);
        $this->money->set($name, $current + $amount);
        $this->money->save();
        return true;
    }
    public function setMoney($player, $amount)
    {
        $name = strtolower($player instanceof Player ? $player->getName(): $player);
        $this->money->set($name,(int)$amount);
        $this->money->save();
        return true;
    }
    public function reduceMoney($player,$amount)
    {
        if($amount <= 0)
            return false;
        $name = strtolower($player instanceof Player ? $player->getName(): $player);
        $current = $this->money->get($name, 0);
        if($amount > $current)
            return false;
        $this->money->set($name, $current - $amount);
        $this->money->save();
        return true;
    }
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args):bool
    {
        switch(strtolower($command->getName()))
        {

        case "addmoney":
            if(!$this->isAdministratorOfVeoZaxAPI($sender))
                return true;
            if(count($args) < 2)
                {
                $sender->sendMessage($this->msg($sender,"addmoney.usage"));
                return true;
            }
            $amount = (int)$args[0];
            if($amount <= 0)
                {
                $sender->sendMessage($this->msg($sender,"addmoney.invalid"));
                return true;
            }
            $target = $this->findPlayer($args[1]);
            if(!$target)
                {
                $sender->sendMessage($this->msg($sender,"addmoney.no_player"));
                return true;
            }
            $this->addMoney($target, $amount);
            $sender->sendMessage($this->msg($sender,"addmoney.success",[$amount, $target->getName()]));
        return true;
        case "takemoney":
            if(!$sender instanceof Player
            ||!$this->isAdministratorOfVeoZaxAPI($sender))
            return true;
            if(count($args) < 2)
                {
                $sender->sendMessage($this->msg($sender,"takemoney.usage"));
                return true;
            }
            $targetPlayer = $this->findPlayer($args[0]);
            if(!$targetPlayer)
                {
                $sender->sendMessage($this->msg($sender,"takemoney.no_player"));
                return true;
            }
            $targetName = $targetPlayer->getName();
            $amountArg = str_replace("amount:", "",
            strtolower($args[1]));
            $targetMoney = $this->myMoney($targetName);
            $amount = ($amountArg === "full") ? $targetMoney : (int)$amountArg;
            if($amount <= 0 || $amount > $targetMoney)
                {
                $sender->sendMessage($this->msg($sender,"takemoney.invalid"));
                return true;
            }
            $this->reduceMoney($targetName, $amount);
            $this->addMoney($sender, $amount);
            $sender->sendMessage($this->msg($sender,"takemoney.success",[$amount, $targetName]));
        return true;
        case "removemoney":
            if(!$this->isAdministratorOfVeoZaxAPI($sender))
                return true;
            if(count($args) < 2)
                {
                $sender->sendMessage($this->msg($sender,"removemoney.usage"));
                return true;
            }
            $targetPlayer = $this->findPlayer($args[0]);
            if(!$targetPlayer)
                {
                $sender->sendMessage($this->msg($sender,"removemoney.no_player"));
                return true;
            }
            $targetName = $targetPlayer->getName();
            $amountArg = str_replace("amount:", "",
            strtolower($args[1]));
            $targetMoney = $this->myMoney($targetName);
            $amount = ($amountArg === "full") ? $targetMoney : (int)$amountArg;
            if($amount <= 0 || $amount > $targetMoney)
                {
                $sender->sendMessage($this->msg($sender,"removemoney.invalid"));
                return true;
            }
            $this->reduceMoney($targetName, $amount);
            $sender->sendMessage($this->msg($sender,"removemoney.success",[$amount, $targetName]));
        return true;
        case "pay":
            if(!$sender instanceof Player)
                return true;
            if(count($args) < 2)
                {
                $sender->sendMessage($this->msg($sender,"pay.usage"));
                return true;
            }
            $now = time();
            $name = strtolower($sender->getName());
            $nextTime = $this->cooldowns->get($name, 0);
            if($now < $nextTime)
                {
                $remaining = $nextTime - $now;
                $sender->sendMessage($this->msg($sender,"pay.cooldown",[$remaining]));
                return true;
            }
            $amount = (int)$args[0];
            if($amount <= 0)
                {
                $sender->sendMessage($this->msg($sender,"pay.invalid"));
                return true;
            }
            $target = $this->findPlayer($args[1]);
            if(!$target)
                {
                $sender->sendMessage($this->msg($sender,"pay.no_player"));
                return true;
            }
            if(strtolower($target->getName()) === $name)
                {
                $sender->sendMessage($this->msg($sender,"pay.self"));
                return true;
            }
            if($this->myMoney($sender) < $amount)
                {
                $sender->sendMessage($this->msg($sender,"pay.insufficient"));
                return true;
            }
            $this->reduceMoney($sender, $amount);
            $this->addMoney($target, $amount);
            $this->cooldowns->set($name, $now + 60);
            $this->cooldowns->save();
            if($target instanceof Player)
                {
                $target->sendTip($this->trans($target,"pay.received_tip",[$amount]));
            }
            foreach($this->getServer()->getOnlinePlayers() as $p)
                {
                $p->sendMessage($this->msg($p,"pay.broadcast",
                [$sender->getName(), $amount, $target->getName()]));
            }
            $sender->sendMessage($this->msg($sender,"pay.success"));
        return true;
        case "paytime":
            if(!$sender instanceof Player)
                return true;
            $name = strtolower($sender->getName());
            $now = time();
            $nextTime = $this->cooldowns->get($name, 0);
            if($now >= $nextTime)
                {
                $sender->sendMessage($this->msg($sender,"paytime.can_pay_now"));
                return true;
            }
            $remaining = $nextTime - $now;
            $sender->sendMessage($this->msg($sender,"paytime.wait",[$remaining]));
        return true;
        case "money":
            if($sender instanceof Player)
                {
                $sender->sendMessage($this->msg($sender,"money.balance",
                [$this->myMoney($sender)]));
            }
        return true;
        case "seemoney":
            if(isset($args[0]))
                {
                $sender->sendMessage($this->msg($sender,"seemoney.balance",
                [$args[0], $this->myMoney($args[0])]));
            }
        return true;
        }
        return false;
    }
}