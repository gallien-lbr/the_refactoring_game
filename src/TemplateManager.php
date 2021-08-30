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

            $placeholders = [
                '[lesson:instructor_link]' => 'instructors/' . $objInstructor->id . '-' . urlencode($objInstructor->firstname),
                '[lesson:summary_html]' =>  Lesson::renderHtml($objLesson),
                '[lesson:summary]' =>  Lesson::renderText($objLesson),
                '[lesson:instructor_name]' =>  $objInstructor->firstname,
            ];

            foreach ($placeholders as $placeholder => $replacement) {
                $text = $placeholderFormatter->replace($placeholder,$replacement,$text);
            }
        }

        if ($lesson->meetingPointId) {
            if (strpos($text, '[lesson:meeting_point]') !== false)
                $text = str_replace('[lesson:meeting_point]', $objMeetingPoint->name, $text);
        }

        if (strpos($text, '[lesson:start_date]') !== false)
            $text = str_replace('[lesson:start_date]', $lesson->start_time->format('d/m/Y'), $text);

        if (strpos($text, '[lesson:start_time]') !== false)
            $text = str_replace('[lesson:start_time]', $lesson->start_time->format('H:i'), $text);

        if (strpos($text, '[lesson:end_time]') !== false)
            $text = str_replace('[lesson:end_time]', $lesson->end_time->format('H:i'), $text);


        if (isset($data['instructor']) and ($data['instructor'] instanceof Instructor))
            $text = str_replace('[instructor_link]', 'instructors/' . $data['instructor']->id . '-' . urlencode($data['instructor']->firstname), $text);
        else
            $text = str_replace('[instructor_link]', '', $text);

        /*
         * USER
         * [user:*]
         */
        $_user = (isset($data['user']) and ($data['user'] instanceof Learner)) ? $data['user'] : $APPLICATION_CONTEXT->getCurrentUser();
        if ($_user) {
            (strpos($text, '[user:first_name]') !== false) and $text = str_replace('[user:first_name]', ucfirst(strtolower($_user->firstname)), $text);
        }

        return $text;
    }
}
