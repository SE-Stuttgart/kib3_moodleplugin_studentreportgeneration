<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * List of Hardcoded DB query information.
 *
 * @package     mod_srg
 * @copyright   2024 University of Stuttgart <kasra.habib@iste.uni-stuttgart.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_srg\local;

use mod_srg\local\report_table;

use stdClass;

/**
 * Class that contains some preset reports based on existing database data.
 */
class report_system {
    /** @var array This Array holds all needed origin tables.*/
    private array $tables;

    /**
     * Create an object to easily access reports and reduce database queries.
     */
    public function __construct() {
        $this->tables = [];
    }


    /**
     * Load the data of the db table "logstore_standard_log" into a local representation as mod_srg\table.
     * @param mixed $USER The current user.
     * @param stdClass $course The course this activity belongs to.
     * @return report_table object, that contains the data.
     */
    private function get_logstore_standard_log_table($USER, $course): report_table {
        $tablename = "logstore_standard_log";
        if (!isset($this->tables[$tablename])) {
            $this->tables[$tablename] = (new report_table([], []))->get_db_records(
                $tablename,
                [
                    "userid" => [$USER->id],
                    "courseid" => [$course->id],
                ],
                [
                    "id",
                    "timecreated",
                    "userid",
                    "courseid",
                    "eventname",
                    "component",
                    "action",
                    "target",
                    "objecttable",
                    "objectid",
                    "contextid",
                    "contextlevel",
                    "contextinstanceid",
                ]
            )
                ->additional_requirement("id")
                ->additional_requirement("timecreated");
        }
        return $this->tables[$tablename];
    }


    /**
     * Load the data of the db table "hvp_xapi_results" into a local representation as mod_srg\table.
     * @param mixed $USER The current user.
     * @param stdClass $course The course this activity belongs to.
     * @return report_table object, that contains the data.
     */
    private function get_hvp_table($USER, $course): report_table {
        $tablename = "hvp_xapi_results";
        if (!isset($this->tables[$tablename])) {
            $this->tables[$tablename] = (new report_table([], []))->get_db_records(
                $tablename,
                [
                    "user_id" => [$USER->id],
                ],
                [
                    "id",
                    "content_id",
                    "interaction_type",
                    "raw_score",
                    "max_score",
                ]
            )
                ->additional_requirement("id")
                ->additional_requirement("content_id");
        }
        return $this->tables[$tablename];
    }


    /**
     * Load the data of the db table "badge_issued" into a local representation as mod_srg\table.
     * @param mixed $USER The current user.
     * @param stdClass $course The course this activity belongs to.
     * @return report_table object, that contains the data.
     */
    private function get_badges_table($USER, $course): report_table {
        $tablename = "badge_issued";
        if (!isset($this->tables[$tablename])) {
            $this->tables[$tablename] = (new report_table([], []))->get_db_records(
                $tablename,
                [
                    "userid" => [$USER->id],
                ],
                [
                    "id",
                    "badgeid",
                ]
            )
                ->additional_requirement("id")
                ->additional_requirement("badgeid");
        }
        return $this->tables[$tablename];
    }


    /**
     * Load the data of the db table "chatbot_history" into a local representation as mod_srg\table.
     * @param mixed $USER The current user.
     * @param stdClass $course The course this activity belongs to.
     * @return report_table object, that contains the data.
     */
    private function get_chatbot_history_table($USER, $course): report_table {
        $tablename = "chatbot_history";
        if (!isset($this->tables[$tablename])) {
            $this->tables[$tablename] = (new report_table([], []))->get_db_records(
                $tablename,
                [
                    "userid" => [$USER->id],
                    "courseid" => [$course->id],
                ],
                [
                    "id",
                    "timecreated",
                    "speaker",
                    "message",
                    "act",
                ]
            )
                ->additional_requirement("id")
                ->additional_requirement("timecreated");
        }
        return $this->tables[$tablename];
    }


    /**
     * This function returns all entries from the course log db table.
     *
     * @param mixed $USER The current user.
     * @param stdClass $course The course this activity belongs to.
     *
     * @return array Table containing set of log data.
     */
    public function get_course_log($USER, $course) {
        $origin = $this->get_logstore_standard_log_table($USER, $course);

        $table = $origin->create_and_get_sub_table(
            [
                "id",
                "timecreated",
                "eventname",
                "component",
                "action",
                "target",
                "objecttable",
                "objectid",
                "contextid",
                "contextlevel",
                "contextinstanceid",
            ]
        )
            ->add_human_time(get_string('time', 'mod_srg'))
            ->add_constant_column(get_string('course_shortname', 'mod_srg'), $course->shortname)
            ->add_constant_column(get_string('course_fullname', 'mod_srg'), $course->fullname);

        return $table->get_table();
    }


    /**
     * This function returns all entries from the course log db table.
     * The entries are grouped by "dedication".
     * This means, entries that are timed close together get grouped together
     * and the time difference in this group is "dedication", how much time was spent on this group.
     *
     * @param mixed $USER The current user.
     * @param stdClass $course The course this activity belongs to.
     *
     * @return array Table containing set of log data.
     */
    public function get_course_dedication($USER, $course) {
        $origin = $this->get_logstore_standard_log_table($USER, $course);

        $table = $origin->create_and_get_sub_table(
            [
                "id",
                "timecreated",
                "courseid",
            ]
        )
            ->add_dedication(get_string('dedication', 'mod_srg'))
            ->add_human_time(get_string('time', 'mod_srg'));

        return $table->get_table();
    }



    /**
     * This function returns all entries from the course log db table that have selected targets and actions.
     * This data is expanded by information not found in the standard log db table.
     *
     * @param mixed $USER The current user.
     * @param stdClass $course The course this activity belongs to.
     *
     * @return array Table containing set of log data.
     */
    public function get_course_module_log($USER, $course) {
        $origin = $this->get_logstore_standard_log_table($USER, $course);

        $table = $origin->create_and_get_sub_table(
            [
                "id",
                "timecreated",
                "eventname",
                "component",
                "action",
                "target",
                "objecttable",
                "objectid",
                "contextid",
                "contextlevel",
                "contextinstanceid",
            ]
        )
            ->additional_requirement("objecttable")
            ->additional_requirement("objectid")
            ->additional_constraint("target", [
                "course_module",
                "course_content",
                "course_bin_item",
                "h5p",
                "attempt",
                "chapter",
                "question",
            ])
            ->additional_constraint("action", ["viewed", "failed", "started", "submitted"])
            ->add_human_time(get_string('time', 'mod_srg'))
            ->add_constant_column("object_name", "")
            ->add_constant_column(get_string('course_shortname', 'mod_srg'), $course->shortname)
            ->add_constant_column(get_string('course_fullname', 'mod_srg'), $course->fullname)
            ->join_with_variable_table(
                "objecttable",
                "objectid",
                ["name" => "object_name"],
                ["book_chapters" => ["title" => "object_name"]]
            )
            ->rename_column("object_name", get_string('object_name', 'mod_srg'));

        return $table->get_table();
    }

    /**
     * This function returns all entries from the course log db table.
     * The entries are grouped by "dedication".
     * This means, entries that are timed close together and belonging to the same component get grouped together
     * and the time difference in this group is "dedication", how much time was spent on this group.
     * This data is expanded by information not found in the standard log db table.
     *
     * @param mixed $USER The current user.
     * @param stdClass $course The course this activity belongs to.
     *
     * @return array Table containing set of log data.
     */
    public function get_course_module_dedication($USER, $course) {
        $origin = $this->get_logstore_standard_log_table($USER, $course);

        $table = $origin->create_and_get_sub_table(
            [
                "id",
                "timecreated",
                "eventname",
                "component",
                "action",
                "target",
                "objecttable",
                "objectid",
                "contextid",
                "contextlevel",
                "contextinstanceid",
            ]
        )
            ->additional_requirement("objecttable")
            ->additional_requirement("objectid")
            ->additional_constraint("target", [
                "course_module",
                "course_content",
                "course_bin_item",
                "h5p",
                "attempt",
                "chapter",
                "question",
            ])
            ->additional_constraint("action", ["viewed", "failed", "started", "submitted"])
            ->add_dedication(get_string('dedication', 'mod_srg'), "component")
            ->add_human_time(get_string('time', 'mod_srg'))
            ->add_constant_column("object_name", "")
            ->add_constant_column(get_string('course_shortname', 'mod_srg'), $course->shortname)
            ->add_constant_column(get_string('course_fullname', 'mod_srg'), $course->fullname)
            ->join_with_variable_table(
                "objecttable",
                "objectid",
                ["name" => "object_name"],
                ["book_chapters" => ["title" => "object_name"]]
            )
            ->rename_column("object_name", get_string('object_name', 'mod_srg'));

        return $table->get_table();
    }


    /**
     * This function returns all entries from the course log db table
     * that have information about the user accessing their grades.
     *
     * @param mixed $USER The current user.
     * @param stdClass $course The course this activity belongs to.
     *
     * @return array Table containing set of log data.
     */
    public function get_grading_interest($USER, $course) {
        $origin = $this->get_logstore_standard_log_table($USER, $course);

        $table = $origin->create_and_get_sub_table(
            [
                "id",
                "timecreated",
                "eventname",
            ]
        )
            ->additional_constraint("eventname", [
                '\mod_assign\event\grading_table_viewed',
                '\mod_assign\event\grading_form_viewed',
                '\gradereport_user\event\grade_report_viewed',
                '\gradereport_overview\event\grade_report_viewed',
                '\gradereport_grader\event\grade_report_viewed',
                '\gradereport_outcomes\event\grade_report_viewed',
                '\gradereport_singleview\event\grade_report_viewed',
            ])
            ->add_human_time(get_string('time', 'mod_srg'))
            ->add_constant_column(get_string('course_shortname', 'mod_srg'), $course->shortname)
            ->add_constant_column(get_string('course_fullname', 'mod_srg'), $course->fullname)
            ->rename_column("eventname", get_string('eventname', 'mod_srg'));

        return $table->get_table();
    }

    /**
     * This function returns all entries from the course log db table
     * that have information about the user using a forum.
     *
     * @param mixed $USER The current user.
     * @param stdClass $course The course this activity belongs to.
     *
     * @return array Table containing set of log data.
     */
    public function get_forum_activity($USER, $course) {
        $origin = $this->get_logstore_standard_log_table($USER, $course);

        $table = $origin->create_and_get_sub_table(
            [
                "id",
                "timecreated",
                "eventname",
                "component",
                "action",
                "target",
                "objecttable",
                "objectid",
            ]
        )
            ->additional_requirement("objecttable")
            ->additional_requirement("objectid")
            ->additional_constraint("component", ["mod_forum"])
            ->add_human_time(get_string('time', 'mod_srg'))
            ->add_constant_column("name", "")
            ->add_constant_column("discussionid", "")
            ->join_with_variable_table(
                "objecttable",
                "objectid",
                ["name" => "name"],
                ["forum_posts" => ["discussion" => "discussionid"]]
            )
            ->join_with_fixed_table(
                "forum_discussions",
                "discussionid",
                ["name" => "name"]
            );

        return $table->get_table();
    }

    /**
     * This function returns all entries from the course log db table
     * that have information about the users interaction with hvp content.
     *
     * @param mixed $USER The current user.
     * @param stdClass $course The course this activity belongs to.
     *
     * @return array Table containing set of log data.
     */
    public function get_hvp($USER, $course) {
        $origin = $this->get_hvp_table($USER, $course);

        $table = $origin->create_and_get_sub_table(
            [
                "id",
                "content_id",
                "interaction_type",
                "raw_score",
                "max_score",
            ]
        )
            ->add_constant_column("course", "")
            ->add_constant_column("object_name", "")
            ->add_constant_column("timecreated", "")
            ->join_with_fixed_table(
                "hvp",
                "content_id",
                ["course" => "courseid", "name" => "object_name", "timecreated" => "timecreated"]
            )
            ->additional_constraint("courseid", $course->id)
            ->add_human_time(get_string('time', 'mod_srg'))
            ->add_constant_column(get_string('course_shortname', 'mod_srg'), $course->shortname)
            ->add_constant_column(get_string('course_fullname', 'mod_srg'), $course->fullname)
            ->rename_column("object_name", get_string('object_name', 'mod_srg'));

        return $table->get_table();
    }

    /**
     * This function returns all entries from the course log db table
     * that have information about the users badges.
     *
     * @param mixed $USER The current user.
     * @param stdClass $course The course this activity belongs to.
     *
     * @return array Table containing set of log data.
     */
    public function get_badges($USER, $course) {
        $origin = $this->get_badges_table($USER, $course);

        $table = $origin->create_and_get_sub_table(
            [
                "id",
                "badgeid",
            ]
        )
            ->add_constant_column("courseid", "")
            ->add_constant_column("object_name", "")
            ->add_constant_column("timecreated", "")
            ->join_with_fixed_table(
                "badge",
                "badgeid",
                ["course" => "courseid", "name" => "object_name", "timecreated" => "timecreated"]
            )
            ->additional_constraint("courseid", [$course->id])
            ->add_human_time(get_string('time', 'mod_srg'))
            ->add_constant_column(get_string('course_shortname', 'mod_srg'), $course->shortname)
            ->add_constant_column(get_string('course_fullname', 'mod_srg'), $course->fullname)
            ->rename_column("object_name", get_string('object_name', 'mod_srg'));

        return $table->get_table();
    }


    /**
     * This function returns all entries from the chatbot_history db table
     * that have information about the users chatbot history.
     *
     * @param mixed $USER The current user.
     * @param stdClass $course The course this activity belongs to.
     *
     * @return array Table containing set of log data.
     */
    public function get_chatbot_history($USER, $course) {
        $origin = $this->get_chatbot_history_table($USER, $course);

        $table = $origin->create_and_get_sub_table(
            [
                "id",
                "timecreated",
                "speaker",
                "message",
                "act",
            ]
        )
            ->add_human_time(get_string('time', 'mod_srg'))
            ->add_constant_column(get_string('course_shortname', 'mod_srg'), $course->shortname)
            ->add_constant_column(get_string('course_fullname', 'mod_srg'), $course->fullname);

        return $table->get_table();
    }
}
