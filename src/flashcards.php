<?php

class flashcards
{
    private const CARD_DIR = '../cards/';
    private const SUPPORTED_LANGUAGES = ['ko', 'de'];
    private const MODES = ['front', 'back', 'mix'];

    private string $dir;

    private array $wrongAnswers = [];

    public function run()
    {
        echo "Welcome to Flashcards \n\n";
        $lang = readline("Select language (ko, de): ");
        $this->validateLang($lang);

        $mode = readline("Select card side to show (front, back, mix): ");
        $this->validateMode($mode);

        $this->dir = self::CARD_DIR . $lang . '/';
        $dictionary = $this->processDictionaries();


        $checks = readline("How many words do you want to test? (max " . count($dictionary) . "): ");
        if (!is_numeric($checks)) {
            exit("non-numeric value - exiting\n");
        }

        $this->runTest($checks, $dictionary, $mode);
    }

    # private

    /**
     * basic loop through the randomly selected words
     * @param $checks
     * @param array $dictionary
     * @param string $mode
     */
    private function runTest($checks, array $dictionary, string $mode)
    {
        $points = 0;
        $words = array_rand($dictionary, $checks);
        shuffle($words);
        switch ($mode){
            case "en":
                foreach ($words as $set) {
                    $set = $dictionary[$set];
                    $points += $this->checkWord($set[1], $set[0]);
                }
                break;
            case "kr":
                foreach ($words as $set) {
                    $set = $dictionary[$set];
                    $points += $this->checkWord($set[0], $set[1]);
                }
                break;
            case "mix":
                foreach ($words as $set) {
                    $set = $dictionary[$set];
                    $mode = rand(0,1);
                    if ($mode === 0) {
                        $points += $this->checkWord($set[0], $set[1]);
                    } else {
                        $points += $this->checkWord($set[1], $set[0]);
                    }
                }
                break;
            default: echo "hehehe";
        }
    
        $percent = ( $points / $checks ) * 100;
        $coloredPoints = $percent >= 80 ? "\033[0;32m $points \033[0m" : ($percent >= 50 ? "\033[1;33m $points \033[0m" : "\033[0;31m $points \033[0m");
        echo "\nTest done: You have answered $coloredPoints of $checks correct!\n";
        $repeat = readline("See wrong answers? (Y/n): ");
        if ($repeat === 'Y' || empty($repeat)) {
            foreach ($this->wrongAnswers as $wrongAnswer) {
                echo "\n\033[0;34m  Question: \033[0m" . $wrongAnswer[0].
                    "\n\033[0;34m  Answer: \033[0m" . $wrongAnswer[1] . "\n\n";
            }
        }
    }

    /**
     * hacky solution for keeping points, sry
     * @param string $question
     * @param string $answer
     * @return int
     */
    private function checkWord(string $question, string $answer): int
    {
        $input = readline("$question: ");
        if ($input === $answer) {
            echo "\033[0;32m ✓ \033[0m\n";
            return 1;
        } else {
            $this->wrongAnswers[] = [$question, $answer];
            echo "\033[0;31m ✗ \033[1m $answer \033[0m\n";
            return 0;
        }
    }

    /**
     * Fetch the levels/sets
     * @return array
     */
    private function processDictionaries(): array
    {
        $levels = readline("Select levels! Enter all selected comma separated. Empty equals all: ");

        $dirs = [];
        if (empty($levels)) {
            # fetch all
            $files = scandir($this->dir);
            unset($files[0]);
            unset($files[1]);

            foreach($files as $file) {
                $dirs = array_merge($this->processFile($file), $dirs);
            }
        } else {
            $levels = explode(",", $levels);
            foreach ($levels as $level) {
                $dirs = array_merge($this->processFile($this->dir . $level . '.json'), $dirs);
            }
        }
        return $dirs;
    }

    /**
     * @param string $file
     * @return array|false[]|string[][]
     */
    private function processFile(string $file): array
    {
        $content = file_get_contents($this->dir. $file);
        return json_decode($content);
    }

    /**
     * @param string $lang
     */
    private function validateLang(string $lang)
    {
        if (!in_array($lang, self::SUPPORTED_LANGUAGES)) {
            exit('Language not supported :( - try one of these: ' . implode(', ', self::SUPPORTED_LANGUAGES));
        }
    }
    
    /**
     * @param string $mode
     */
    private function validateMode(string $mode)
    {
        if(!in_array($mode, self::MODES)) {
            exit("invalid mode - exiting\n");
        }
    }
}

(new flashcards())->run();