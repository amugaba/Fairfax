<?php
/**
 * Provides variables for 2015 categories
 */
function getGroupCodes($group) {
    if($group == 1)
        return ['A2A', 'A3A', 'A4'];
    if($group == 2)
        return ['T3', 'T4A', 'T5', 'T2'];
    if($group == 3)
        return ['D3A', 'D9A', 'D17', 'D15'];
    if($group == 4)
        return ['X1', 'X8'];
    if($group == 5)
        return ['A5', 'S3'];
    if($group == 6)
        return ['B20', 'B22', 'CB3', 'CB1'];
    if($group == 7)
        return ['B15', 'B16'];
    if($group == 8)
        return ['B2A', 'B10A', 'B11', 'W5'];
    if($group == 10)
        return ['fruitveg', 'H7', 'H3', 'H20', 'H2'];
    if($group == 11)
        return ['M5', 'M1', 'M2'];
    if($group == 12)
        return ['C2', 'C11', 'C12', 'extracurric'];
    if($group == 13)
        return ['PF9', 'PS3', 'PC2', 'PC11','LS4'];
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
}

