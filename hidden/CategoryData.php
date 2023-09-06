<?php
require_once "HighlightGroup.php";
require_once "DataService.php";
/**
 * Provides variables for 2015 categories
 */
function getHighlightGroup($cat, $dataset, $year)
{
    $connector = "";
    if ($cat == 1) {
        $title = "Alcohol";
        if($dataset == DataService::EIGHT_TO_TWELVE) {
            $qCodes = ['A2A', 'A3A', 'A4'];
            $labels = ['Lifetime Alcohol Use', 'Past Month Alcohol Use', 'Past Month Binge Drinking (5+ Drinks in a Row)'];
        }
        else {
            $qCodes = ['A2B', 'A3B'];
            $labels = ['Lifetime Alcohol Use', 'Past Month Alcohol Use'];
        }
        $explanation = "<p>The Youth Survey asks about use of a wide variety of licit and illicit substances.  The highlights page focuses on alcohol, the most commonly used substance by Fairfax County youth.</p>
        <p>To learn about other substances or to compare alcohol use with other behaviors, <a href='graphs.php'>Explore the Data</a>.</p>";
    }
    else if ($cat == 2) {
        $title = "Tobacco";
        if($dataset == DataService::EIGHT_TO_TWELVE) {
            $qCodes = ['T3', 'T4A', 'T5', 'T2', 'T6'];
            $labels = ['Lifetime Cigarette Use', 'Past Month Cigarette Use', 'Past Month E-Cigarette Use', 'Past Month Smokeless Tobacco Use', 'Past Month Hookah Use'];
            $explanation = "<p>The Youth Survey asks about use of a wide variety of licit and illicit substances.  The highlights page focuses on tobacco, including e-cigarettes.</p>
            <p>To learn about other substances or to compare tobacco use with other behaviors, <a href='graphs.php'>Explore the Data</a>.</p>";
        }
        else {
            $qCodes = ['T3', 'T4B'];
            $labels = ['Lifetime Cigarette Use', 'Past Month Cigarette Use'];
            $explanation = "<p>The Youth Survey asks about use of a wide variety of licit and illicit substances.  The highlights page focuses on tobacco.</p>
            <p>To learn about other substances or to compare tobacco use with other behaviors, <a href='graphs.php'>Explore the Data</a>.</p>";
        }
    }
    else if ($cat == 3) {
        $title = "Drugs";
        if($dataset == DataService::EIGHT_TO_TWELVE) {
            $qCodes = ['D3A', 'D9A', 'D17', 'D15'];
            $labels = ['Past Month Marijuana Use', 'Past Month Inhalant Use', 'Past Month Painkiller Use (without doctor\'s order)', 'Past Month Heroin Use'];
        }
        else {
            $qCodes = ['D3B', 'D9B', 'D25'];
            $labels = ['Past Month Marijuana Use', 'Past Month Inhalant Use', 'Past Month Other Illegal Drug Use'];
        }
        $explanation = "<p>The Youth Survey asks about use of a wide variety of licit and illicit substances.  The highlights page focuses on selected substances of interest to the Fairfax County community.</p>
        <p>To learn about other substances or to compare substance use with other behaviors, <a href='graphs.php'>Explore the Data</a>.</p>";
    } else if ($cat == 20) {
        $title = "Vaping";
        if($dataset == DataService::EIGHT_TO_TWELVE && $year >= 2018) {
            $qCodes = ['V1', 'V2', 'V3', 'V4', 'vaping'];
            $labels = ['Lifetime Vape Use', 'Past Month Vaping Nicotine', 'Past Month Vaping Marijuana', 'Past Month Vaping Flavoring', 'Past Month Vaping Any of the Above'];
            $explanation = "<p>The Youth Survey asks about vaping nicotine, marijuana, and flavoring in the past 30 days, as well as ever having vaped.</p>
        <p>To compare vaping with substance use or other behaviors, <a href='graphs.php'>Explore the Data</a>.</p>";
        }
        else if($dataset == DataService::SIXTH && $year >= 2021) {
            $qCodes = ['V1', 'V2', 'V3', 'V4', 'vaping'];
            $labels = ['Lifetime Vape Use', 'Past Month Vaping Nicotine', 'Past Month Vaping Marijuana', 'Past Month Vaping Flavoring', 'Past Month Vaping Any of the Above'];
            $explanation = "<p>The Youth Survey asks about vaping nicotine, marijuana, and flavoring in the past 30 days, as well as ever having vaped.</p>
        <p>To compare vaping with substance use or other behaviors, <a href='graphs.php'>Explore the Data</a>.</p>";
        }
        else {
            $qCodes = [];
            $labels = [];
            $explanation = "";
        }
    } else if ($cat == 4) {
        $title = "Sexual Health";
        if($dataset == DataService::EIGHT_TO_TWELVE) {
            $qCodes = ['X1', 'X8'];
            $labels = ['Lifetime Sexual Intercourse', 'Lifetime Oral Sex'];
            $explanation = "<p>The Youth Survey asks about students' sexual behavior, including preventive behaviors (condom use).
            Related questions addressing aggression in relationships are reported in the <a href='highlights.php?cat=7'>Dating Aggression</a> category.</p>
            <p>To learn more about behaviors related to sexual health, <a href='graphs.php'>Explore the Data</a>.</p>";
        }
        else {
            //display message that the 6th grade survey doesn't ask about this
            $qCodes = [];
            $labels = [];
            $explanation = "";
        }
    } else if ($cat == 5) {
        $title = "Vehicle Safety";
        if($dataset == DataService::EIGHT_TO_TWELVE) {
            $qCodes = ['A5', 'S3', 'S4'];
            $labels = ['Past Month Driving after Drinking', 'Past Month Texting while Driving', 'Past Month Fell Asleep while Driving'];
            $explanation = "<p>The Youth Survey asks about behaviors that are associated with unsafe driving practices, such as driving
            after drinking, texting while driving, and falling asleep while driving.</p><p style='font-style: italic; text-decoration: underline'>Data are for 12th grade students only.</p>
            <p>To compare vehicle safety with other behaviors, <a href='graphs.php'>Explore the Data</a>.</p>";
        }
        else {
            //display message that the 6th grade survey doesn't ask about this
            $qCodes = [];
            $labels = [];
            $explanation = "";
        }
    } else if ($cat == 6) {
        $title = "Bullying and Cyberbullying";
        if($dataset == DataService::EIGHT_TO_TWELVE) {
            $qCodes = ['B20', 'B22', 'CB3', 'CB2'];
            $labels = ['Past Year Bullied Someone at School', 'Past Year Had Been Bullied at School', 'Past Year Cyberbullied a Schoolmate', 'Past Year Had Been Cyberbullied by a Schoolmate'];
        }
        else {
            $qCodes = ['B20', 'B22', 'CB3', 'CB2'];
            $labels = ['Past Year Bullied Someone at School', 'Past Year Had Been Bullied at School', 'Past Year Cyberbullied a Schoolmate', 'Past Year Had Been Cyberbullied by a Schoolmate'];
        }
        $explanation = "<p>The Youth Survey asks questions about both bullying in-person and bullying online (called cyberbullying).</p>
        <p>Information specifically about bullying at school is available on the highlights page, while a broader range of activities (out-of-school behavior) is also available: <a href='graphs.php'>Explore the Data</a>.</p>";
    } else if ($cat == 7) {
        $title = "Dating Aggression";
        if($dataset == DataService::EIGHT_TO_TWELVE) {
            $qCodes = ['B15', 'B16', 'B24', 'B25'];
            $labels = ['Had a Partner that Always Wanted to Know Whereabouts', 'Had a Partner that Verbally Abused', 'Past Year Was Physically Hurt by Partner', 'Past Year Was Forced to Do Sexual Things by Partner'];
            $explanation = "<p>There are a variety of behaviors that might be classified as dating aggression, or that might signify 
            a risk of dating aggression. These range from a partner always wanting to know his or her partner's whereabouts to pressuring a partner to have sex.</p>
            <p>To learn more about behaviors related to dating aggression, including physical abuse, <a href='graphs.php'>Explore the Data</a>.</p>";
        }
        else {
            //display message that the 6th grade survey doesn't ask about this
            $qCodes = [];
            $labels = [];
            $explanation = "";
        }
    } else if ($cat == 8) {
        $title = "Harassment and Aggressive Behaviors";
        if($dataset == DataService::EIGHT_TO_TWELVE) {
            $qCodes = ['B2A', 'B10A', 'B11', 'W5', 'B14A', 'B14B'];
            $labels = ["Past Year Insulted Someone's Race or Culture", 'Past Year Had Race or Culture Insulted', 'Past Year Had Been Sexually Harassed', 'Past Month Carried a Weapon',
                'Past Year Was Verbally Abused by Parent', 'Past Year Was Physically Hurt by Parent'];
            $explanation = "<p>The Youth Survey asks about harassment and aggression in a variety of forms, both verbal and physical. The highlights page 
            provides information on racial/cultural harassment and sexual harassment. It also provides information on youth who reported carrying a weapon
            and youth who reported verbal and/or physical abuse by parent or adult at home.</p>
            <p>To learn more about other behaviors and experiences related to harassment and aggression, <a href='graphs.php'>Explore the Data</a>.</p>";
        }
        else {
            $qCodes = ['B2A', 'B10A', 'W5', 'B14A', 'B14B'];
            $labels = ["Past Year Insulted Someone's Race or Culture", 'Past Year Had Race or Culture Insulted', 'Past Month Carried a Weapon',
                'Past Year Was Verbally Abused by Parent', 'Past Year Was Physically Hurt by Parent'];
            $explanation = "<p>The Youth Survey asks about harassment and aggression in a variety of forms, both verbal and physical. The highlights page 
            provides information on racial/cultural harassment and on youth who reported carrying a weapon.</p>
            <p>To learn more about other behaviors and experiences related to harassment and aggression, <a href='graphs.php'>Explore the Data</a>.</p>";
        }
    }
    else if ($cat == 10) {
        $title = "Nutrition";
        $qCodes = ['fruitveg', 'fruitveg2021', 'H7', 'RF31'];
        $labels = ['Ate Fruits and Vegetables at least 5 Times per Day', 'Ate Fruits and Vegetables at least 5 Times per Day', 'Drank No Soda during Past Week', 'Past Month Went Hungry (Food Insecurity)'];
        $explanation = "<p>The Youth Survey asks about eating fruits and vegetables, drinking sweetened beverages, and going hungry due to food insecurity.</p>
        <p>To learn more about behaviors related to nutrition, including energy drink and sports drink consumption, <a href='graphs.php'>Explore the Data</a>.</p>";
    }
    else if ($cat == 11) {
        $title = "Mental Health";
        if($dataset == DataService::EIGHT_TO_TWELVE) {
            if($year >= 2021) {
                $qCodes = ['M5A', 'M1', 'M2', 'M4'];
                $labels = ['Past Month Felt Stress Most or All of the Time', 'Felt Sad or Hopeless for Two or More Weeks in a Row', 'Past Year Considered Suicide', 'Past Year Attempted Suicide'];
            }
            else {
                $qCodes = ['M5', 'M1', 'M2', 'M4'];
                $labels = ['Past Month Had High Stress', 'Felt Sad or Hopeless for Two or More Weeks in a Row', 'Past Year Considered Suicide', 'Past Year Attempted Suicide'];
            }
            $explanation = "<p>The Youth Survey asks about a variety of different aspects related to mental health. This page 
            highlights students who reported high levels of stress, those who felt sad or helpless two or more weeks in a row 
            (which may indicate risk for depression), those who considered attempting suicide, and those who attempted suicide.</p>
        <p>To learn more about these topics, as well as suicidal ideation, <a href='graphs.php'>Explore the Data</a>.</p>";
        }
        else {
            if($year >= 2021) {
                $qCodes = ['M5A', 'M1'];
                $labels = ['Past Month Felt Stress Most or All of the Time', 'Felt Sad or Hopeless for Two or More Weeks in a Row'];
            }
            else {
                $qCodes = ['M5', 'M1'];
                $labels = ['Past Month Had High Stress', 'Felt Sad or Hopeless for Two or More Weeks in a Row'];
            }
            $explanation = "<p>The Youth Survey asks about a variety of different aspects related to mental health. This page 
            highlights students who reported high levels of stress and those who felt sad or helpless two or more weeks in a row 
            (which may indicate risk for depression).</p>
        <p>To learn more about these topics, <a href='graphs.php'>Explore the Data</a>.</p>";
        }
    }
    else if ($cat == 12) {
        $title = "Civic Engagement and Time Use";
        $qCodes = ['C2', 'C11', 'C12', 'extracurric', 'C4'];
        $labels = ['Volunteered to do Community Service Regularly', 'Did Homework for 1+ Hours per Day', 'Went to Work for 1+ Hours per Day', 'Did Extracurriculars for 1+ Hours per Day',
            'Past Year Served as a Leader in a Group'];
        $explanation = "<p>The Youth Survey asks questions related to civic engagement and use of time outside of school hours, 
            including volunteering for community service and time spent on homework, working at a job, and participating in extracurricular 
            activities. This page shows the percentage of students who volunteer regularly and spend 1 or more hours on selected activities outside of school hours.</p>
        <p>To see more specific level of engagement of students, such as number of hours worked or number of times volunteered, <a href='graphs.php'>Explore the Data</a>.</p>";
    }
    else if ($cat == 13) {
        $title = "Assets that Build Resiliency";
        $qCodes = ['PF9', 'C2', 'LS4', 'C10', 'PS3', 'PC2'];
        $labels = ['Can Ask Parents for Help with Personal Problems', 'Performs Community Service Once a Month or More', 'Feels It Is Important to Accept Responsibility for Actions',
            'Does Extracurricular Activities Once a Month or More', 'Teachers Recognize Good Work', 'Could Talk to Adults in Community about Something Important'];
        $explanation = "<p>The Youth Survey asks about assets that are strengths in young people, their families, schools, and 
            communities that help them thrive in health, in school, and daily life, and in a safe environment.  The more assets an individual 
            has in his or her life, the fewer risk behaviors are reported.  This highlights page focuses on selected assets that build resiliency in youth.</p>
        <p>To learn about other assets or to compare prevalence of risk behaviors with assets, <a href='graphs.php'>Explore the Data</a> 
        under the following categories:  School, Family, Community Support, Civic Engagement, and Self/Peer Perception.</p>";
    }
    else if ($cat == 14) {
        $title = "Physical Activity";
        $qCodes = ['H3', 'H20', 'H2', 'H1'];
        $labels = ['Had One Hour of Physical Activity at least 5 Days per Week', 'Got Eight or More Hours of Sleep on a School Night', 'Used Computer or Played Video Games for 3+ Hours per Day',
            'Watched TV for 3+ Hours per Day'];
        $explanation = "<p>The Youth Survey asks about level of physical activity and other questions related to physical health including getting 8 or more hours of sleep, time spent on computer for non-school activities, and time spent on TV.</p>
        <p>To learn more about behaviors related to physical health, <a href='graphs.php'>Explore the Data</a>.</p>";
    }
    else {
        die("Category chosen is invalid.");
    }

    $var = new HighlightGroup();
    $var->title = $title;
    $var->explanation = $explanation;
    $var->codes = $qCodes;
    $var->labels = $labels;
    return $var;
}

