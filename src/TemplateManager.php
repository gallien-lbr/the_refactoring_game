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
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();

        /** @var Lesson $lesson */
        $lesson = (isset($data['lesson']) and $data['lesson'] instanceof Lesson) ? $data['lesson'] : null;

        if ($lesson) {
            $objLesson = LessonRepository::getInstance()->getById($lesson->id);
            $objMeetingPoint = MeetingPointRepository::getInstance()->getById($lesson->meetingPointId);
            $objInstructor = InstructorRepository::getInstance()->getById($lesson->instructorId);

            $lessonPlaceholders = [
                PlaceholderHelper::PH_LESSON_INSTRUCTOR_LINK =>  self::INSTRUCTOR_SEGMENT . $objInstructor->id . '-' . urlencode($objInstructor->firstname),
                PlaceholderHelper::PH_LESSON_SUMMARY_HTML => Lesson::renderHtml($objLesson),
                PlaceholderHelper::PH_LESSON_SUMMARY => Lesson::renderText($objLesson),
                PlaceholderHelper::PH_LESSON_INSTRUCTOR_NAME => $objInstructor->firstname,
                PlaceholderHelper::PH_LESSON_MEETING_POINT => $objMeetingPoint->name,
            ];

            foreach ($lessonPlaceholders as $placeholder => $replacement) {
                if (!$lesson->meetingPointId && $placeholder === PlaceholderHelper::PH_LESSON_MEETING_POINT) {
                    continue;
                }
                $text = PlaceholderHelper::replace($placeholder, $replacement, $text);
            }

            $text =  PlaceholderHelper::replace(PlaceholderHelper::PH_LESSON_START_DATE, $lesson->start_time->format('d/m/Y'), $text);
            $text = PlaceholderHelper::replace(PlaceholderHelper::PH_LESSON_START_TIME, $lesson->start_time->format('H:i'), $text);
            $text = PlaceholderHelper::replace(PlaceholderHelper::PH_LESSON_END_TIME, $lesson->end_time->format('H:i'), $text);
        }

        if (isset($data['instructor']) and ($data['instructor'] instanceof Instructor)) {
            $text = str_replace(PlaceholderHelper::PH_INSTRUCTOR_LINK, self::INSTRUCTOR_SEGMENT  . $data['instructor']->id . '-' . urlencode($data['instructor']->firstname), $text);
        } else {
            $text = str_replace(PlaceholderHelper::PH_INSTRUCTOR_LINK, '', $text);
        }

        /**
         * USER
         * [user:*]
         */
        if ($user = (isset($data['user']) and ($data['user'] instanceof Learner)) ? $data['user'] : $APPLICATION_CONTEXT->getCurrentUser()) {
            $text = PlaceholderHelper::replace(PlaceholderHelper::PH_USER_FIRSTNAME,  ucfirst(strtolower($user->firstname)), $text);
        }

        return $text;
    }
}
