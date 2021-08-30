<?php

namespace App;

use App\Context\ApplicationContext;
use App\Entity\Instructor;
use App\Entity\Learner;
use App\Entity\Lesson;
use App\Entity\Template;
use App\Repository\InstructorRepository;
use App\Repository\LessonRepository;
use App\Repository\MeetingPointRepository;

class TemplateManager
{
    const INSTRUCTOR_SEGMENT = 'instructors/';
    const ENTITY_LESSON = 'lesson';
    const ENTITY_INSTRUCTOR = 'instructor';
    const ENTITY_USER = 'user';

    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    private function computeText($text, array $data)
    {
        /** @var Lesson $lesson */
        if ($lesson = $this->getLesson($data)) {
            $text = $this->computeTextLesson($lesson, $text);
        }
        /** @var Instructor $instructor */
        $replacement = '';

        if ($instructor = $this->getInstructor($data)) {
            $replacement = self::INSTRUCTOR_SEGMENT . $instructor->id . '-' . urlencode($instructor->firstname);
        }
        $text = str_replace(PlaceholderHelper::PH_INSTRUCTOR_LINK, $replacement, $text);

        /** @var Learner $user */
        if ($user = $this->getUser($data)) {
            $text = PlaceholderHelper::replace(PlaceholderHelper::PH_USER_FIRSTNAME, ucfirst(strtolower($user->firstname)), $text);
        }

        return $text;
    }

    /**
     * @param $data
     * @return Lesson|null
     */
    protected function getLesson($data)
    {
        return (isset($data[self::ENTITY_LESSON]) and $data[self::ENTITY_LESSON] instanceof Lesson) ? $data[self::ENTITY_LESSON] : null;
    }

    /**
     * @param $data
     * @return Instructor|null
     */
    protected function getInstructor($data)
    {
        return isset($data[self::ENTITY_INSTRUCTOR]) and ($data[self::ENTITY_INSTRUCTOR] instanceof Instructor) ? $data[self::ENTITY_INSTRUCTOR] : null;
    }

    /**
     * @param $data
     * @return Learner
     */
    protected function getUser($data)
    {
        return (isset($data[self::ENTITY_USER]) and ($data[self::ENTITY_USER] instanceof Learner)) ? $data[self::ENTITY_USER] : ApplicationContext::getInstance()->getCurrentUser();
    }

    /**
     * @param $lesson
     * @param $text
     * @return string
     */
    protected function computeTextLesson($lesson, $text): string
    {
        $objLesson = LessonRepository::getInstance()->getById($lesson->id);
        $objMeetingPoint = MeetingPointRepository::getInstance()->getById($lesson->meetingPointId);
        $objInstructor = InstructorRepository::getInstance()->getById($lesson->instructorId);

        $lessonPlaceholders = [
            PlaceholderHelper::PH_LESSON_INSTRUCTOR_LINK => self::INSTRUCTOR_SEGMENT . $objInstructor->id . '-' . urlencode($objInstructor->firstname),
            PlaceholderHelper::PH_LESSON_SUMMARY_HTML => Lesson::renderHtml($objLesson),
            PlaceholderHelper::PH_LESSON_SUMMARY => Lesson::renderText($objLesson),
            PlaceholderHelper::PH_LESSON_INSTRUCTOR_NAME => $objInstructor->firstname,
            PlaceholderHelper::PH_LESSON_MEETING_POINT => $objMeetingPoint->name,
            PlaceholderHelper::PH_LESSON_START_DATE => $lesson->start_time->format('d/m/Y'),
            PlaceholderHelper::PH_LESSON_START_TIME => $lesson->start_time->format('H:i'),
            PlaceholderHelper::PH_LESSON_END_TIME => $lesson->end_time->format('H:i'),
        ];

        foreach ($lessonPlaceholders as $placeholder => $replacement) {
            if (!$lesson->meetingPointId && $placeholder === PlaceholderHelper::PH_LESSON_MEETING_POINT) {
                continue;
            }
            $text = PlaceholderHelper::replace($placeholder, $replacement, $text);
        }

        return $text;
    }
}
