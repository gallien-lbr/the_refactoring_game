<?php

namespace App;

class PlaceholderFormatter
{
    public const ALGO_REPLACE1 = 'algo_replace1';

    const PH_LESSON_SUMMARY_HTML = '[lesson:summary_html]';
    const PH_LESSON_INSTRUCTOR_LINK = '[lesson:instructor_link]';
    const PH_LESSON_SUMMARY = '[lesson:summary]';
    const PH_LESSON_INSTRUCTOR_NAME ='[lesson:instructor_name]';
    const PH_LESSON_MEETING_POINT = '[lesson:meeting_point]';
    const PH_LESSON_START_DATE = '[lesson:start_date]';
    const PH_LESSON_START_TIME = '[lesson:start_time]';
    const PH_LESSON_END_TIME = '[lesson:end_time]';
    const PH_USER_INSTRUCTOR_NAME = '[user:first_name]';

    public const PH_ARRAY_MAP = [
        self::PH_LESSON_SUMMARY_HTML => self::ALGO_REPLACE1,
        self::PH_LESSON_INSTRUCTOR_LINK => self::ALGO_REPLACE1,
        self::PH_LESSON_SUMMARY => self::ALGO_REPLACE1,
        self::PH_LESSON_INSTRUCTOR_NAME => self::ALGO_REPLACE1,
        self::PH_LESSON_MEETING_POINT => self::ALGO_REPLACE1,
        self::PH_LESSON_START_DATE => self::ALGO_REPLACE1,
        self::PH_LESSON_START_TIME => self::ALGO_REPLACE1,
        self::PH_LESSON_END_TIME => self::ALGO_REPLACE1,
        self::PH_USER_INSTRUCTOR_NAME => self::ALGO_REPLACE1,
    ];


    public function replace(string $placeholder, string $replacement, string $text){
        if(!array_key_exists($placeholder, self::PH_ARRAY_MAP)){
            throw new \Exception('Key is invalid');
        }
        $algo = self::PH_ARRAY_MAP[$placeholder];
        return $this->$algo($text, $placeholder,$replacement);
    }


    private function algo_replace1($text, $placeholder,$replacement){
        if (strpos($text, $placeholder) !== false) {
            $text = str_replace($placeholder, $replacement, $text);
        }
        return $text;
    }


}