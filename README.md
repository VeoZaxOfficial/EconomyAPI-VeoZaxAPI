# 💰 EconomyAPI

**A lightweight economy system for [VeoZaxAPI](https://github.com/VeoZaxOfficial/Multiversion-API-0.14.x-0.15.10-1.1.x-VeoZax-API-Build-3.0).**

Every player gets a balance they can check, spend and send to others with `/money` and `/pay`, while operators manage money with simple commands. Works as a `.phar` or as a plain source folder.

> Made by **VeoZax** for VeoZaxAPI - PocketMine-MP.

---

## ✨ Features

- **Per-player balances** - an account is created automatically the first time a player joins
- **Pay other players** - `/pay <amount> <player>` sends money from your own balance
  - 60-second cooldown between payments (check it with `/paytime`)
  - every payment is announced to everyone online, and the receiver gets an on-screen tip
- **Balance checks** - `/money` for yourself, `/seemoney <player>` for anyone
- **Operator money tools** - add, remove or take money from players, including `full` to use a player's entire balance
- **Partial name matching** - type the start of a name (`/pay 100 Vei`) instead of the full name
- **Developer API** - other plugins can read and change balances
- **Folder or phar** - runs from a `.phar` or straight from the source folder
- Zero dependencies, no database - data is stored in `money.yml`

---

## 📋 Requirements

- A server using **VeoZaxAPI**

---

## 📥 Installation

### As a `.phar`
1. Download the `EconomyAPI` plugin from the repo as Zip or from MediaFire.
- MediaFire Download Link = [EconomyAPI.zip](YOUR_MEDIAFIRE_LINK_HERE)
2. Drop it into your server's `plugins/` folder.
3. Restart the server.

### As a source folder
1. Download this repository as zip.
2. Put the `EconomyAPI` folder into your server's `plugins/` folder.
   The folder must contain `plugin.yml` and a `src/` directory directly inside it.
3. Open `veozaxapi.yml` in your server's root folder and enable folder loading:
   ```yaml
   developer:
     folder-plugin-loader: true
   ```
4. Restart the server. (A reload is not enough - this setting is read at startup.)

---

## 💬 Commands

| Command | Description | Who can use it |
|---|---|---|
| `/money` | Check your own balance | Everyone |
| `/seemoney <player>` | Check another player's balance | Everyone |
| `/pay <amount> <player>` | Pay another online player from your balance (60s cooldown) | Everyone |
| `/paytime` | See how long until you can `/pay` again | Everyone |
| `/addmoney <amount> <player>` | Add money to a player's balance | Operators |
| `/removemoney <player> <amount\|full>` | Remove money from a player (`full` removes their whole balance) | Operators |
| `/takemoney <player> <amount\|full>` | Take money from a player and add it to **your** balance (in-game only) | Operators |

The `amount:` prefix is also accepted, so `/removemoney Steve amount:500` works too.

## 🔑 Permissions

| Permission | Description | Default |
|---|---|---|
| `economyapi.use` | Use `/money`, `/seemoney`, `/pay` and `/paytime` | everyone |
| `economyapi.admin` | Use `/addmoney`, `/removemoney` and `/takemoney` | `op` |

---

## ⚙️ How it works

1. A player joins and an account with a balance of **0** is created for them.
2. Operators give out money with `/addmoney`, or other plugins reward players through the API.
3. Players can send money to each other with `/pay`, then wait 60 seconds before the next payment.
4. Every change is saved to `money.yml` straight away.

All messages are built into the plugin and can be edited in `EconomyAPI.php` (the `$messages` array).

---

## 🗂️ Data & configuration

- Balances are stored in `plugins/EconomyAPI/money.yml` (`lowercase-name: amount`).
- `/pay` cooldowns are stored in `plugins/EconomyAPI/cooldowns.yml`.
- There is no `config.yml` - the plugin has no options yet.

---

## 📝 Notes

- New players start with a balance of **0** and there is no setting to change that yet.
- `/pay`, `/removemoney` and `/takemoney` only work on **online** players, matched by the start of their name.
- Balances are whole numbers.

---

## 🧩 For developers

Other plugins can use EconomyAPI like this:

```php
$economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");

if($economy !== null){
    $balance = $economy->myMoney($player);   // balance (Player object or name)
    $economy->addMoney($player, 500);        // false if amount <= 0
    $economy->reduceMoney($player, 200);     // false if amount <= 0 or not enough money
    $economy->setMoney($player, 1000);       // set an exact balance
}
```

`getMoney()` does the same as `myMoney()`.

---

## 🤝 Contributing

Issues and pull requests are welcome.

If you're reporting a bug, please include your PocketMine-MP version and relevant server log output.

---

## Credits

Developed by **[VeoZax](https://github.com/VeoZaxOfficial)**

---

## License

Released under the [MIT License](LICENSE). Feel free to use, modify, and redistribute with credit to the author VeoZax.

---

## 🔗 Links

<p align="left">
  <a href="https://info.veozax.xyz/"><img src="https://img.shields.io/badge/Website-000000?style=for-the-badge&logo=googlechrome&logoColor=white" alt="Website"/></a>
  <a href="https://www.youtube.com/channel/UC33-nbRHQn-3H56aQc0sanA"><img src="https://img.shields.io/badge/YouTube-FF0000?style=for-the-badge&logo=youtube&logoColor=white" alt="YouTube"/></a>
  <a href="https://discord.gg/dCzgPYam2J"><img src="https://img.shields.io/badge/Discord-5865F2?style=for-the-badge&logo=discord&logoColor=white" alt="Discord"/></a>
  <a href="https://www.instagram.com/veozax"><img src="https://img.shields.io/badge/Instagram-E4405F?style=for-the-badge&logo=instagram&logoColor=white" alt="Instagram"/></a>
  <a href="https://www.tiktok.com/@veozaxofficial"><img src="https://img.shields.io/badge/TikTok-000000?style=for-the-badge&logo=tiktok&logoColor=white" alt="TikTok"/></a>
  <a href="https://www.facebook.com/VeoZax/"><img src="https://img.shields.io/badge/Facebook-1877F2?style=for-the-badge&logo=facebook&logoColor=white" alt="Facebook"/></a>
  <a href="https://github.com/VeoZaxOfficial"><img src="https://img.shields.io/badge/GitHub-181717?style=for-the-badge&logo=github&logoColor=white" alt="GitHub"/></a>
</p>
