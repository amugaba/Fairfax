<?php
require_once "DataService.php";
/**
 * Provides variables for 2015 categories
 */
function getGroupCodes($group, $dataset) {
    if($group == 1) {
        if($dataset == DataService::EIGHT_TO_TWELVE)
            return ['A2A', 'A3A', 'A4'];
        else
            return ['A2B', 'A3B'];
    }
    if($group == 2) {
        if($dataset == DataService::EIGHT_TO_TWELVE)
            return ['T3', 'T4A', 'T5', 'T2'];
        else
            return ['T3', 'T4B'];
    }
    if($group == 3) {
        if($dataset == DataService::EIGHT_TO_TWELVE)
            return ['D3A', 'D9A', 'D17', 'D15'];
        else
            return ['D3B', 'D9B', 'D25'];
    }
    if($group == 4) {
        if($dataset == DataService::EIGHT_TO_TWELVE)
            return ['X1', 'X8'];
        else
            return [];
    }
    if($group == 5) {
        if($dataset == DataService::EIGHT_TO_TWELVE)
            return ['A5', 'S3'];
        else
            return [];
    }
    if($group == 6) {
        if($dataset == DataService::EIGHT_TO_TWELVE)
            return ['B20', 'B22', 'CB3', 'CB2'];
        else
            return ['B20', 'B22', 'CB3', 'CB2'];
    }
    if($group == 7) {
        if($dataset == DataService::EIGHT_TO_TWELVE)
            return ['B15', 'B16'];
        else
            return [];
    }
    if($group == 8) {
        if($dataset == DataService::EIGHT_TO_TWELVE)
            return ['B2A', 'B10A', 'B11', 'W5'];
        else
            return ['B2A', 'B10A', 'W5'];
    }
    if($group == 10) {
        if($dataset == DataService::EIGHT_TO_TWELVE)
            return ['fruitveg', 'H7', 'H3', 'H20', 'H2'];
        else
            return ['fruitveg', 'H7', 'H3', 'H2'];
    }
    if($group == 11) {
        if($dataset == DataService::EIGHT_TO_TWELVE)
            return ['M5', 'M1', 'M2', 'M4'];
        else
            return ['M5', 'M1'];
    }
    if($group == 12) {
        return ['C2', 'C11', 'C12', 'extracurric'];
    }
    if($group == 13) {
        if($dataset == DataService::EIGHT_TO_TWELVE)
            return ['PF9', 'PS3', 'PC2', 'PC11','LS4'];
        else
            return ['PF9', 'PS3', 'PC2', 'LS4'];
    }
    if($group == 20) {
        if($dataset == DataService::EIGHT_TO_TWELVE)
            return ['V1', 'V2', 'V3', 'V4'];
        else
            return ['V1', 'V2', 'V3', 'V4'];
    }
}
function getGroupName($group) {
    if($group == 1)
        return "Alcohol";
    if($group == 2)
        return "Tobacco";
    if($group == 3)
        return "Drugs";
    if($group == 4)
        return "Sexual Health";
    if($group == 5)
        return "Vehicle Safety";
    if($group == 6)
        return "Bullying and Cyberbullying";
    if($group == 7)
        return "Dating Aggression";
    if($group == 8)
        return "Harassment and Aggressive Behaviors";
    if($group == 10)
        return "Nutrition and Physical Activity";
    if($group == 11)
        return "Mental Health";
    if($group == 12)
        return "Civic Engagement and Time Use";
    if($group == 13)
        return "Assets that Build Resiliency";
    if($group == 20)
        return "Vaping";
}

