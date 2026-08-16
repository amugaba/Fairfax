<?php

class VariableNotes
{
    public static function getNotes(string $code) {
        $note = "";
        switch ($code) {
            case "fruitveg2021": $note = "The question and answer options were changed in 2021 and direct comparison between 2021 and previous years' data is not recommended."; break;
        }
        return $note;
    }
}