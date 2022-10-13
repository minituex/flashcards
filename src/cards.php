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
        echo "Updating a card: \n";
        $search = readline("Search term (full front or back): ");
        $matches = array_merge($this->findMatches($search, 'front'), $this->findMatches($search, 'back'));

        $updates = [];
        echo "Found " . count($matches) . " matches for your search term. \n";
        foreach ($matches as $card) {
            echo "Match found: \n";
            echo "Level ".$card['level'].", Question: " . $card['question'] .", Answer: " . $card['answer'] . PHP_EOL;
            $edit = readline("Update? (Y/n): ");
            if ( $edit === 'Y' || empty($edit)) {
                $q = readline("New front?: ");
                $a = readline("New back?: ");
                $updates[] = [
                    'level' => $card['level'],
                    'key' => $card['question'],
                    'value' => $card['answer'],
                    'q' => empty($q) ? $card['question'] : $q ,
                    'a' => empty($a) ? $card['answer'] : $a
                ];
            }
        }
        $this->updateDict($updates);
        echo "Card(s) updated - done! \n";
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
        $questions = $this->findMatches($first, 'front');
        $answers = $this->findMatches($second, 'back');

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

    /**
     * find matches by term and side of card (front, back)
     * @param string $term
     * @param string $side
     * @return array[]
     */
    private function findMatches(string $term, string $side): array
    {
        $matches = [];
        if ($side === 'front') {
            foreach ($this->dictionary as $level => $cards) {
                if (array_key_exists($term, $cards)) {
                    $matches[] = ['level' => $level,'question' => $term, 'answer' => $cards[$term]];
                }
            }
        } else {
            foreach ($this->dictionary as $level => $cards) {
                if (in_array($term, $cards)) {
                    $card = array_filter($cards, fn($c) => $c === $term);
                    foreach ($card as $q => $a) {
                        $matches[] = ['level' => $level, 'question' => $q, 'answer' => $term];
                    }
                }
            }
        }
        return $matches;
    }

    /**
     * update card(s) according to user input
     * @param array $cards
     */
    private function updateDict(array $cards)
    {
        foreach ($cards as $card) {
            $file = self::CARD_DIR . $card['level'] .'.json';
            if ($card['key'] !== $card['q'] || $card['value'] !== $card['a']) {
                #update needed
                $level = json_decode(file_get_contents($file));
                foreach ($level as &$dictCard) {
                    if ($dictCard[0] !== $card['key']) {continue;}
                    $dictCard[0] = $card['q'];
                    $dictCard[1] = $card['a'];
                }
                file_put_contents($file, json_encode($level, JSON_UNESCAPED_UNICODE));
            }
        }
    }
}
(new cards())->run();