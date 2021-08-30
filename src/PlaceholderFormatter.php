<?php

namespace App;

class PlaceholderFormatter
{

    const PH_LESSON_SUMMARY_HTML = '[lesson:summary_html]';
    const PH_LESSON_INSTRUCTOR_LINK = '[lesson:instructor_link]';
    const PH_LESSON_SUMMARY = '[lesson:summary]';
    const PH_LESSON_INSTRUCTOR_NAME ='[lesson:instructor_name]';
    const PH_LESSON_MEETING_POINT = '[lesson:meeting_point]';
    const PH_LESSON_START_DATE = '[lesson:start_date]';
    const PH_LESSON_START_TIME = '[lesson:start_time]';
    const PH_LESSON_END_TIME = '[lesson:end_time]';
    const PH_USER_FIRSTNAME = '[user:first_name]';
    const PH_INSTRUCTOR_LINK = '[instructor_link]';

    public function replace(string $placeholder, string $replacement, string $text){
        if (strpos($text, $placeholder) !== false) {
            $text = str_replace($placeholder, $replacement, $text);
        }
        return $text;
    }
}