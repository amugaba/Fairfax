<?php
/**
 * Parent class of CutoffVariable and MultiVariable.
 * A Variable is a question that has some number of answers.
 */
class Variable
{
    public int $id;
    public int $year;
    public string $is8to12;     //Is this variable in the 8th-12th grade dataset or the 6th grade dataset?
    public string $code;        //Identifier that matches the code in the SPSS dataset
    public ?string $question;   //Full text of the question
    public ?string $summary;    //Short text to be displayed in dropdown menus
    public ?int $category;      //Category ID i.e. Alcohol=1
    public ?int $display_order; //Order in which to display the question in the dropdown menus
    public bool $has_trends;    //Can the question be transformed into a binary Yes/No by setting a cutoff point
    public ?int $low_cutoff;    //Answers that are this value or higher are considered "Yes" in the cutoff. Null means no low cutoff point. Anything below the high cutoff is "Yes".
    public ?int $high_cutoff;   //Answers that are this value or lower are considered "Yes" in the cutoff. Null means no high cutoff point. Anything above the low cutoff is "Yes".
    public ?int $total_cutoff;  //If null, all respondents are counted in the total (denominator). Otherwise, only students that answered this value or higher are included in the total.
    public ?string $cutoff_summary; //Short text to be displayed in dropdown menus on Trends and 3TS pages
    public ?string $cutoff_tooltip; //Text to be displayed in when hovering points in the graph on Trends and 3TS pages
    public ?string $answer0, $answer1, $answer2, $answer3, $answer4, $answer5, $answer6, $answer7, $answer8, $answer9, $answer10, $answer11, $answer12, $answer13, $answer14,
        $answer15, $answer16, $answer17, $answer18, $answer19, $answer20, $answer21, $answer22, $answer23, $answer24, $answer25, $answer26;
    public array $labels = [];   //The text of the question's answer options

    public array $counts = [];       //Number of respondents that chose each answer option
    public array $percents = [];     //Percentage of respondents that chose each answer option
    public array $totals = [];       //Number of respondents that answered the question
}