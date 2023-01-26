MarbleBlast-Prod
--

Production PHP scripts used for the Marble Blast Platinum / Platinum Quest backend. Presented in (mostly) unabridged form here, for reference purposes.

## Structure

- `leader/` Marble Blast Platinum Online (legacy) scripts, at least the ones still in use in 2021. (~2012-2017)
- `leader/admin` Marble Blast Platinum Online Administration website, including all the various legacy admin tools that generally don't work anymore. (~2012-2021)
- `leader/MP_Master` Marble Blast Multiplayer Master Server source, this just juggles game servers into the database. (~2012-2021)
- `pq/demo` PlatinumQuest Demo statistics tracking scripts. (~2017)
- `pq/leader/admin` PlatinumQuest Online admin scripts, only one was ever created because I got too good at using CLI through PhpStorm.  (~2017-2021)
- `pq/api` PlatinumQuest Online public API, interfaced with by the game via `stats.cs`. (~2017-2021)
- `pq/cli` PlatinumQuest Online CLI scripts used via aforementioned PhpStorm. These are mostly single-shot and will never work or need to be run again. (~2017-2021)
- `pq/web` PlatinumQuest Online Administrator tools, namely the Ratings Editor. (~2017-2021)
- `pq/ratings` PlatinumQuest Online Ratings Viewer webpage (publicly accessible). (~2017-2021)
- `pq/stats` PlatinumQuest Online statistics and Leaderboards webpages  (publicly accessible). (~2017-2021)

## History

Most of the scripts in `leader/` were written between 2012 and 2014, leading up to the release of Marble Blast Platinum 1.50 and its new Leaderboards system on marbleblast.com. I was 15 years old at the time, and the code quality should make that pretty clear. Half or more of those scripts are no longer available online, due to their features being removed (challenges, super challenges, old chat systems, etc). I may publish them some day, but it's unlikely.

Not included here is the source to Webchat, in `leader/socketserver.php` and related files. It just seemed too fragile and terrible to ever release to the world, at least while it's still running. If Webchat ever goes down for good, let me know and I'll post the source here.

The `pq/` scripts were written between 2016 and 2021, and are actually using json and php objects like someone knew what they were doing, at least somewhat. All of these are available online right now, though I'm not sure what changes, if any, have been made since 2021.

## Running

These scripts were almost always Developed In Production and as such, were never intended to be used in any other environments. Due to this, they are incredibly strongly tied to the specific server running the entire marbleblast.com website, and will almost certainly never work in another environment (the closest I ever got was running a duplicate copy on my local machine, back in like 2013). Additionally, there's an entire installation of Joomla! that is not included here (for GPL reasons), without which the entire system will not work.

I never managed to grab a proper database schema dump before resigning, so that's not available either. There were a couple stored procedures in there that are not included here, because of said lack of dumping. Not that a schema would fix the above paragraph of problems, but it would have been nice. If you really really want one, ask RandomityGuy, and prepare to get told off because no, of course not.

## Security

The codebase was written and has been mildly glanced over by someone who was Okay At PHP Security back in 2018. I actually found a couple bugs just doing that (the bugs have been patched out in this release, enjoy guessing where they were!), and there are likely more hiding in here somewhere. If you find any, I would request you inform RandomityGuy and not abuse them to pop the site, pretty please? Just submit a fake world record score using the API like normal (which is apparently not part of the security model LOL) and get your honorary banned title.

I attempted to scrub all of the tokens. If that ends up not being the case, ~~feel free to post funny messages from the Bot account~~ you should probably tell someone to revoke the keys :)

## License

MIT License, in case you are crazy enough to want to use this anywhere near your code.
