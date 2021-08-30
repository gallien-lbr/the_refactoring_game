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

            $placeholderFormatter = new PlaceholderFormatter();

            $lessonPlaceholders = [
                PlaceholderFormatter::PH_LESSON_INSTRUCTOR_LINK => 'instructors/' . $objInstructor->id . '-' . urlencode($objInstructor->firstname),
                PlaceholderFormatter::PH_LESSON_SUMMARY_HTML => Lesson::renderHtml($objLesson),
                PlaceholderFormatter::PH_LESSON_SUMMARY => Lesson::renderText($objLesson),
                PlaceholderFormatter::PH_LESSON_INSTRUCTOR_NAME => $objInstructor->firstname,
            ];

            foreach ($lessonPlaceholders as $placeholder => $replacement) {
                $text = $placeholderFormatter->replace($placeholder, $replacement, $text);
            }
        }

        if ($lesson->meetingPointId) {
            $text = $placeholderFormatter->replace(PlaceholderFormatter::PH_LESSON_MEETING_POINT, $objMeetingPoint->name, $text);
        }

        $text = $placeholderFormatter
            ->replace(PlaceholderFormatter::PH_LESSON_START_DATE, $lesson->start_time->format('d/m/Y'), $text);
        $text = $placeholderFormatter
            ->replace(PlaceholderFormatter::PH_LESSON_START_TIME, $lesson->start_time->format('H:i'), $text);
        $text = $placeholderFormatter
            ->replace(PlaceholderFormatter::PH_LESSON_END_TIME, $lesson->end_time->format('H:i'), $text);

        if (isset($data['instructor']) and ($data['instructor'] instanceof Instructor)) {
            $text = str_replace(PlaceholderFormatter::PH_INSTRUCTOR_LINK, 'instructors/' . $data['instructor']->id . '-' . urlencode($data['instructor']->firstname), $text);
        } else {
            $text = str_replace(PlaceholderFormatter::PH_INSTRUCTOR_LINK, '', $text);
        }

        /*
         * USER
         * [user:*]
         */
        $_user = (isset($data['user']) and ($data['user'] instanceof Learner)) ? $data['user'] : $APPLICATION_CONTEXT->getCurrentUser();
        if ($_user) {
            (strpos($text, PlaceholderFormatter::PH_USER_FIRSTNAME) !== false) and $text = str_replace(PlaceholderFormatter::PH_USER_FIRSTNAME, ucfirst(strtolower($_user->firstname)), $text);
        }

        return $text;
    }
}
