<?php

class cards
{
    private const CARD_DIR = '../cards/';
    private int $level;
    private array $dictionary;

    public function run()
    {
        $options = getopt("c::u::d::", ["level:"]);
        $option = $this->validateOptions($options);
        $this->fetchDictionary();
        $this->$option();
    }

    /**
     * create new card
     */
    private function c()
    {
        echo "Add a new card: \n";
        $first = readline("Front: ");
        $second = readline("Back: ");
        $this->checkForDuplicate($first, $second);
        if(!file_exists(self::CARD_DIR . $this->level . ".json")) {
            touch(self::CARD_DIR . $this->level . ".json");
        }

        $file = json_decode(file_get_contents(self::CARD_DIR . $this->level . ".json"));
        $file[] = [$first, $second];
        file_put_contents(self::CARD_DIR . $this->level . ".json", json_encode($file, JSON_UNESCAPED_UNICODE));
    }

    /**
     * update existing card(s)
     */
    private function u()
    {
        echo "Not yet implemented \n";
    }

    /**
     * delete card
     */
    private function d()
    {
        echo "Not yet implemented \n";
    }

    private function validateOptions(array $options): string
    {
        if (count($options)  != 2){
            exit("Wrong number of arguments - exiting!\n");
        }
        if (!isset($options['level'])) {
            exit("--level must be set - exiting!\n");
        }
        $this->level = (int)$options['level'];
        unset($options['level']);
        return array_key_first($options);
    }

    /**
     * gather all cards, return by level as key, value (question, answer)
     */
    private function fetchDictionary()
    {
        $files = scandir(self::CARD_DIR);
        unset($files[0]);
        unset($files[1]);

        $dict = [];
        foreach ($files as $file) {
            $content = json_decode(file_get_contents(self::CARD_DIR . $file));
            $level = [];
            foreach ($content as $card) {
                $level[$card[0]] = $card[1];
            }
            $key = explode(".", $file)[0];
            $dict[$key] = $level;
        }
        $this->dictionary = $dict;
    }

    /**
     * check if an entry exists while adding new cards
     * @param $first
     * @param $second
     */
    private function checkForDuplicate($first, $second)
    {
        $questions = [];
        $answers = [];
        foreach ($this->dictionary as $level => $cards) {
            if (array_key_exists($first, $cards)) {
                $questions[] = ['level' => $level, 'answer' => $cards[$first]];
            }

            if (in_array($second, $cards)) {
                $card = array_filter($cards, fn($c) => $c === $second);
                foreach ($card as $q => $a) {
                    $answers[] = ['level' => $level, 'question' => $q];
                }
            }
        }

        if (!empty($questions)) {
            echo "Card(s) with this question already in dictionary: \n";
            foreach ($questions as $q) {
                echo "Level ".$q['level'].", Question: $first, Answer: " . $q['answer'] . PHP_EOL;
            }

            if (readline("Add anyway? (Y/n) ") !== "Y") {
                exit("Aborting\n");
            }
        }

        if (!empty($answers)) {
            echo "Card(s) with this answer already in dictionary: \n";

            foreach ($answers as $a) {
                echo "Level ".$a['level'].", Question ".$a['question'].", Answer: " . $second . PHP_EOL;
            }

            if (readline("Add anyway? (Y/n) ") !== "Y") {
                exit("Aborting\n");
            }
        }

    }


}
(new cards())->run();