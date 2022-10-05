<?php

class flashcards
{
    private const CARD_DIR = '../cards/';

    public function run()
    {
        echo "Welcome to Flashcards \n\n";

        $mode = readline("Select laguage mode (kr, en, mix): ");
        if (!$this->validateMode($mode)) {
            exit("invalid mode - exiting\n");
        }

        $dictionary = $this->processDictionaries();

        $checks = readline("How many words do you want to test? ");
        if (!is_numeric($checks)) {
            exit("non-numeric value - exiting\n");
        }

        $this->runTest($checks, $dictionary, $mode);
    }

    # private

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
        echo "Test done: You have answered $points of $checks correct!\n";
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
            echo "\033[0;32m Correct! \033[0m\n";
            return 1;
        } else {
            echo "\033[0;31m Wrong :( $answer \033[0m\n";
            return 0;
        }
    }

    private function processDictionaries(): array
    {
        $levels = readline("Select levels! Enter all selected comma separated. Empty equals all: ");

        $dirs = [];
        if (empty($levels)) {
            # fetch all
            $files = scandir(self::CARD_DIR);
            unset($files[0]);
            unset($files[1]);

            foreach($files as $file) {
                $dirs = array_merge($this->processFile($file), $dirs);
            }
        } else {
            $levels = explode(",", $levels);
            foreach ($levels as $level) {
                $dirs = array_merge($this->processFile(self::CARD_DIR . $level . '.json'), $dirs);
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
        $content = file_get_contents(self::CARD_DIR. $file);
        return json_decode($content);
    }

    private function validateMode(string $mode): bool
    {
        $validModes = ['kr', 'en', 'mix'];
        return in_array($mode, $validModes);
    }
}

(new flashcards())->run();