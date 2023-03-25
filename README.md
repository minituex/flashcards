# flashcards

I could not find an online tool for custom flashcards that was free or usable. So I wrote a stupid php script.

No it's not pretty - but it does the job, kinda.


Now with multi-lang support!!

## Usage

In general, the words are divided into levels and ordered by language.

### Study
 `php flashcards.php` and follow the instructions.
You will be asked to provide the language, the mode and select levels.


* Languages available: ko (Korean) and de (German)
* Modes: Front (show front, enter back), Back (show back, enter front), Mix (Randomly either show front or back)
* Levels: see in cards folder for available levels, leaving this empty will select all

### Vocab

* **ADD:** `php cards.php -c --level <> --lang <>` enter front and back. will add the word to the end of the file
* **UPDATE:** `php cards.php -u --lang` and follow the instructions
* **Search:** `php cards.php -s --lang` and follow the instructions
* **DELET:** `php cards.php -d --lang` and follow the instructions (only number mode is implemented rn)



