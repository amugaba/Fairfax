<?php

class VariableNotes
{
    public static function getNotes(string $code) {
        $note = "";
        switch ($code) {
            case "Q_serv1":
            case "Q_serv2": $note = "The gambling helpline number changed in 2024.";
        }
        return $note;
    }
}