<?php

namespace App\Api\V1\Traits;

use Jenssegers\Optimus\Optimus;

trait HandlesUniqueUids
{

    /**
    * Generate a uid for institutions eg MOF-3186-8767
     */
    public function generateUniqueUid($institution)
    {
        $initials = $this->getInitials($institution);
        $uid = $this->getUid();

        return $this->getUniqueUid($initials, $uid);
    }
    /**
    * get a initials from passed institution name
     */
    protected function getInitials(string $institution): string
    {
        $words = preg_split("/\s+/", $institution);
        $initials = '';
        //if institution name is less than 2 words, use the first three alphabets
        if (count($words) < 2) {
            $word = preg_replace("/[^a-zA-Z]+/", "", $words[0]);               
            $initials = substr($word, 0, 4);
        } else {
            foreach ($words as $word) {
                $word = preg_replace("/[^a-zA-Z]+/", "", $word); 
                if($word && !$this->wordShouldBeIgnored($word)) {
                    $initials .= $word[0];                
                }
            }
            $initials = substr($initials, 0, 4);            
        }

        return $initials;
    }
    /**
    * get uid with php Optimus
     */
    protected function getUid(): string
    {
        $optimus = new Optimus(1134789437, 1923279893, 493166570);
        
        $uid = strtotime(\Carbon\Carbon::now(1));
        $uid = $optimus->encode($uid + mt_rand(11111111, 55555555));
        $uid = (string)$uid;
        $uid = substr($uid, 0, 8); //get at most 8 characters of th uid
        $uid = rtrim(chunk_split($uid, 4, '-'), '-');

        return $uid;
    }

    protected function getUniqueUid(string $initials, string $uid): string
    {
        $uniqueUid = strtoupper($initials) . '-' . $uid;

        if ($this->where('code', $uniqueUid)->count() > 0) {
            $this->getUniqueUid($initials, $uid);
        }

        return $uniqueUid;
    }
    protected function wordShouldBeIgnored($_word) {
        $_word = strtolower($_word);
        $ignoredWords = $this->ignoredWords();
    
        return in_array($_word, $ignoredWords);
    }
    protected function ignoredWords() {
        return [
            'the', 'to', 'for', 'and', 'of', 'too', 'as', 'is', 'be'
        ];
    }
}
