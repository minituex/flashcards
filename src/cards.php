<?php

class cards
{
    private const CARD_DIR = '../cards/';
    private int $level;
    private array $dictionary;
    private string $lang;
    private string $dir;

    public function run()
    {
        $options = getopt("c::u::d::s::", ["level:", "lang:"]);
        $option = $this->validateOptions($options);
        $this->dir = self::CARD_DIR . $this->lang . '/';
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
        if(!file_exists($this->dir . $this->level . ".json")) {
            touch($this->dir . $this->level . ".json");
        }

        $file = json_decode(file_get_contents($this->dir . $this->level . ".json"));
        $file[] = [$first, $second];
        file_put_contents($this->dir . $this->level . ".json", json_encode($file, JSON_UNESCAPED_UNICODE));
        exit(0);
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

            echo "\n    Match found:";
            echo "\n\033[0;34m    Level ".$card['level'].", Question: " . $card['question'] .", Answer: " . $card['answer'] ."\033[0m\n\n";
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
        $mode = readline("Select a mode (numerical n, search s): ");
        if ($mode === "n") {
            $level = intval(readline("Select a level: ")); //default 0 -> error
            if ($level === 0) {
                exit("Faulty input - exiting.");
            }
            $dict = json_decode(file_get_contents($this->dir . $level . ".json"));
            $sections = array_chunk($dict, 10);
            foreach ($sections as $section) {

                exit();
            }
        }
    }

    /**
     * search card
     */
    private function s()
    {
        $search = readline("Search for a specific card or term: ");

        $matches = array_merge($this->findMatches($search, 'front'), $this->findMatches($search, 'back'));
        foreach ($matches as $card) {
            echo "\n  Level: ". $card['level'] .
            "\n\033[0;34m  Question: \033[0m" . $card['question'].
            "\n\033[0;34m  Answer: \033[0m" . $card['answer'] . "\n\n";
        }
        exit();
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
     * gather all cards, return by level as key, value (question, answer)
     */
    private function fetchDictionary()
    {
        $files = scandir($this->dir);
        unset($files[0]);
        unset($files[1]);

        $dict = [];
        foreach ($files as $file) {
            $content = json_decode(file_get_contents($this->dir . $file));
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
     * update card(s) according to user input
     * @param array $cards
     */
    private function updateDict(array $cards)
    {
        foreach ($cards as $card) {
            $file = $this->dir . $card['level'] .'.json';
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

    /**
     * validate input params
     * -c --level
     * -u
     * -d
     * -s
     * @param array $options
     * @return string
     */
    private function validateOptions(array $options): string
    {
        if (!isset($options['lang']))
        {
            echo("Language selection missing - exiting!\n");
            $this->helpText();
        }
        $this->lang = $options['lang'];
        unset($options['lang']);

        if (isset($options['c'])) {
            if (count($options)  != 2){
                echo("Wrong number of arguments - exiting!\n");
                $this->helpText();
            }
            if (!isset($options['level'])) {
                echo("--level must be set - exiting!\n");
                $this->helpText();
            }
            $this->level = (int)$options['level'];
            unset($options['level']);
        } else {
            if (count($options)  != 1){
                echo("Wrong number of arguments - exiting!\n");
                $this->helpText();
            }
        }
        return array_key_first($options);
    }

    private function helpText()
    {
        echo "Usage: \n";
        echo "Create new card: -c --level <> --lang <> \n";
        echo "Update card: -u --lang<>\n";
        echo "Delete card: -d --lang<>\n";
        echo "Search card: -s --lang<>\n";
        exit();
    }
}
(new cards())->run();