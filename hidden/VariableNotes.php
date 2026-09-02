<?php

class VariableNotes
{
    public static function getNotes(string $code) {
        $note = "";
        switch ($code) {
            case "fruitveg2021": $note = "The question and answer options were changed in 2021 and direct comparison between 2021 and previous years' data is not recommended."; break;
            case "D32B": ;
            case "D32C": ;
            case "D32D": ;
            case "D32E": ;
            case "D32F": ;
            case "D32G": ;
            case "D32H": ;
            case "D32I": ;
            case "D32J": ;
            case "D32K": $note = "Only students who used prescription painkillers without a doctor’s order or used street opioids were asked."; break;
            case "X5": ;
            case "X6A": ;
            case "X7A": ;
            case "X7B": ;
            case "X7C": ;
            case "X7D": ;
            case "X7E": ;
            case "X7F": ;
            case "X7G": $note = "Only students who ever had sex were asked."; break;
            case "S11A": ;
            case "S11B": ;
            case "S11C": ;
            case "S11D": ;
            case "S11E": ;
            case "S11F": ;
            case "S11G": ;
            case "S11H": ;
            case "S11I": ;
            case "S11J": $note = "Only students who answered they don’t always wear a seat belt were asked."; break;
            case "RG2": $note = " Only students who participated in one or more gambling activities in the past year were asked."; break;
        }
        return $note;
    }
}