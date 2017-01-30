<?php
/**
 * Provides variables for 2015 categories
 */
function getCategoryVariable($cat)
{
    if ($cat == 1) {
        $title = "Substance Use";
        $qCodes = ['A2A', 'A3A', 'A4', 'D2A'];
        $labels = ['Lifetime Alcohol Use', 'Past-Month Alcohol Use', 'Binge Drinking<br>(5+ Drinks in a Row)', 'Lifetime Marijuana Use'];
        $tooltips = ['The % of students who reported at least 1 occasion of alcohol use ever in their lives.',
            'The % of students who reported at least 1 occasion of alcohol use in the past 30 days.',
            'The % of students who reported at least 1 occasion of drinking 5 or more drinks in a row in the past two weeks.',
            'The % of students who reported at least 1 occasion of marijuana use ever in their lives.'];
        $lowCutoffs = [2, 2, 2, 2];
        $highCutoffs = [null, null, null, null];
        $totalCutoffs = [null, null, null, null];
        $explanation = "<p>The Youth Survey asks about use of a wide variety of licit and illicit substances. The highlights page
        focuses on two of the most-commonly-used psychoactive substances among youth: alcohol (including data on binge drinking) and marijuana.</p>
        <p>To learn about other substances or to compare substance use with other behaviors, <a href='graphs.php'>Explore All Questions</a>.</p>";
    } else if ($cat == 2) {
        $title = "Sexual Activity";
        $qCodes = ['X1', 'X8'];
        $labels = ['Lifetime Sexual<br>Intercourse', 'Lifetime Oral Sex'];
        $tooltips = ['The % of students who reported having sexual intercourse at least once in their lives.',
            'The % of students who reported having oral sex at least once in their lives.'];
        $lowCutoffs = [1, 1];
        $highCutoffs = [1, 1];
        $totalCutoffs = [null, null];
        $explanation = "<p>The Youth Survey asks about students' sexual behavior, including preventive behaviors (condom use).
        Related questions addressing aggression in relationships are reported in the <a href='category.php?cat=5'>Dating Aggression</a> category.</p>
        <p>To learn about other sexual behaviors, <a href='graphs.php'>Explore All Questions</a>.</p>";
    } else if ($cat == 3) {
        $title = "Vehicle Safety";
        $qCodes = ['A5', 'S3'];
        $labels = ['Driving after Drinking', 'Texting while Driving'];
        $tooltips = ['The % of students who reported at least 1 occasion of driving after drinking in the past 30 days.',
            'The % of students who reported at least 1 occasion of texting or e-mailing while driving a car or other vehicle in the past 30 days.'];
        $lowCutoffs = [3, 3];
        $highCutoffs = [null, null];
        $totalCutoffs = [null, null];
        $explanation = "<p>The Youth Survey asks about behaviors that are associated with unsafe driving practices, such as driving
        after drinking and texting while driving. Data are for 12th grade students only.</p>
        <p>To learn more about vehicle safety, <a href='graphs.php'>Explore All Questions</a>.</p>";
    } else if ($cat == 4) {
        $title = "Bullying and Cyberbullying";
        $qCodes = ['B20', 'B22', 'CB3', 'CB2'];
        $labels = ['Bullied Someone<br>at School', 'Been Bullied at School', 'Cyberbullied<br>Someone at School', 'Been Cyberbullied<br>at School'];
        $tooltips = ['The % of students who reported having bullied someone on school property within the past 12 months.',
            'The % of students who reported having been bullied on school property within the past 12 months.',
            'The % of students who reported having cyberbullied someone in the past year.',
            'The % of students who reported having been cyberbullied in the past year.'];
        $lowCutoffs = [1, 1, 2, 2];
        $highCutoffs = [1, 1, null, null];
        $totalCutoffs = [null, null, null, null];
        $explanation = "<p>The Youth Survey asks questions about both bullying in-person and bullying online (called cyberbullying).</p>
        <p>Information specifically about bullying at school is available on the highlights page, while a broader range of activities (out-of-school behavior) is also available: <a href='graphs.php'>Explore All Questions</a>.</p>";
    } else if ($cat == 5) {
        $title = "Dating Aggression";
        $qCodes = ['B15', 'B25'];
        $labels = ['Partner Always Wants<br>to Know Whereabouts', 'Partner Physically<br>Forces Sex'];
        $tooltips = ['The % of students who reported ever having a partner in a dating or serious relationship who always wanted to know their whereabouts.',
            'The % of students who reported that someone with whom they were going out or were dating forced them to do sexual things they did not want to do in the past 12 months.'];
        $lowCutoffs = [1, 3];
        $highCutoffs = [1, null];
        $totalCutoffs = [null, 2];
        $explanation = "<p>There are a variety of behaviors that might be classified as dating aggression, or that might signify a risk of dating aggression.
        These range from a partner physically forcing someone to have sexual intercourse to someone always wanting to know his or her partner’s whereabouts.</p>
        <p>To learn more about behaviors related to dating aggression, <a href='graphs.php'>Explore All Questions</a>.</p>";
    } else if ($cat == 6) {
        $title = "Other Aggressive Behaviors";
        $qCodes = ['B2A', 'B10A', 'W5'];
        $labels = ["Insulted Someone's<br>Race or Culture", 'Had Race or<br>Culture Insulted', 'Carried a Weapon'];
        $tooltips = ['The % of students who reported that they had said something bad about someone’s race or culture in the past year.',
            'The % of students who reported that someone had said something bad about their race or culture in the past year.',
            'The % of students who reported carrying a weapon such as a gun, knife, or club in the past 30 days.'];
        $lowCutoffs = [2, 2, 2];
        $highCutoffs = [null, null, null];
        $totalCutoffs = [null, null, null];
        $explanation = "<p>Aggression can take on a variety of forms, both verbal and physical. The highlights page provides information both on youth
        who had their race or culture insulted, and those who insulted others’ race or culture.  It also provides information on youth who carried a weapon.</p>
        <p>Data about additional behaviors or experiences indicating aggression are available: <a href='graphs.php'>Explore All Questions</a>.</p>";
    } else if ($cat == 7) {
        $title = "Physical Activity and Rest";
        $qCodes = ['H3', 'H3', 'H20', 'H1', 'H2'];
        $labels = ['One Hour of Physical Activity<br>at least 1 Day per Week', 'One Hour of Physical Activity<br>at least 5 Days per Week',
            'Eight or More Hours of Sleep', 'Watches TV for<br> 3+ Hours per Day', 'Uses Computer or Plays Video<br>Games for 3+ Hours per Day'];
        $tooltips = ['The % of students who reported being physically active for at least 60 minutes on at least 1 day in the past 7 days.',
            'The % of students who reported being physically active for at least 60 minutes on at least 5 days in the past 7 days.',
            'The % of students who reported getting at least 8 hours of sleep on an average school night.',
            'The % of students who reported watching at least 3 hours of TV on an average school day.',
            'The % of students who reported playing video or computer games or using a computer for something that was not school work at least 3 hours on an average school day.'];
        $lowCutoffs = [2, 6, 5, 5, 5];
        $highCutoffs = [null, null, null, null, null];
        $totalCutoffs = [null, null, null, null, null];
        $explanation = "<p>The Youth Survey provides data on a variety of interlinked health behaviors related to physical activity and rest.
        Highlights include both frequency of physical activity across selected timeframes as well as indicators of inactivity and information about adequate sleep.</p>
        <p>Additional data are available in this category by choosing to <a href='graphs.php'>Explore All Questions</a>.</p>";
    } else if ($cat == 8) {
        $title = "Nutrition and Weight Loss Behaviors";
        $qCodes = ['fruitveg', 'H7', 'RF31'];
        $labels = ['Ate Fruits and Vegetables<br>at least 5 Times per Day', 'Drank No Soda<br>during Past Week', 'Went Hungry at least Once<br>during Past Month'];
        $tooltips = ['The % of students who ate fruits (excluding juice) and vegetables an average of 5 times per day over the past week.',
            'The % of students who did not drink soda (pop) in the past 7 days, not including diet soda.',
            'The % of students who went hungry in the past month (sometimes, most of the time, or always) because there was not enough food in the home.'];
        $lowCutoffs = [4.95, 1, 3];
        $highCutoffs = [null, 1, null];
        $totalCutoffs = [null, null, null];
        $explanation = "<p>The Youth Survey asks about eating fruits and vegetables, drinking sugared drinks, and hunger.</p>
        <p>To see additional nutrition data, including weight loss behaviors, go to <a href='graphs.php'>Explore All Questions</a>.</p>";
    } else if ($cat == 9) {
        $title = "Mental Health";
        $qCodes = ['M5', 'M1', 'M2'];
        $labels = ['High Stress', 'Felt Sad or Hopeless for<br>Two or More Weeks in a Row', 'Attempted Suicide'];
        $tooltips = ['The % of students who reported a stress level in the past month of 8 or higher on a scale from 1 to 10.',
            'The % of students who reported, during the past year, having felt so sad or hopeless almost every day for two weeks or more in a row that they stopped doing some usual activities.',
            'The % of students who reported having actually attempted suicide in the past 12 months.'];
        $lowCutoffs = [8, 1, 1];
        $highCutoffs = [null, 1, 1];
        $totalCutoffs = [null, null, null];
        $explanation = "<p>The Youth Survey provides data about a variety of different aspects related to mental health. This page highlights
        students who reported high levels of stress, those who felt sad or helpless two or more weeks in a row (which may indicate risk for depression), and those who attempted suicide.</p>
        <p>Additional data on this topic are available at <a href='graphs.php'>Explore All Questions</a>.</p>";
    } else if ($cat == 10) {
        $title = "Extracurricular Activities and Civic Behaviors";
        $qCodes = ['C13', 'C11', 'C12', 'C2'];
        $labels = ['Did Extracurriculars<br>for 1+ Hour per Day', 'Did Homework<br>for 1+ Hour per Day', 'Went to Work<br>for 1+ hour per Day', 'Volunteered for<br>Community Service'];
        $tooltips = ['The % of students who reported staying after school to participate in a team, club, or program for at least 1 hour on an average school day.',
            'The % of students who reported doing at least 1 hour of homework outside of school on an average school day.',
            'The % of students who reported going to work (e.g., a job) for at least 1 hour on an average school day.',
            'The % of students who reported volunteering to do community service about once a month or more during the past year.'];
        $lowCutoffs = [4, 4, 4, 3];
        $highCutoffs = [null, null, null, null];
        $totalCutoffs = [null, null, null, null];
        $explanation = "<p>The Youth Survey asks about a variety of behaviors that indicate civic engagement or diligence, including
        completion of homework, working at a job, volunteering in the community, and participating in extracurricular activities.
        This page shows the percentage of students with a moderate level of engagement (1+ hour of work or at least one time volunteering).</p>
        <p>To see the exact levels of engagement of students, such as number of hours worked or number of times volunteered, <a href='graphs.php'>Explore All Questions</a>.</p>";
    } else {
        die("Category chosen is invalid.");
    }

    $var = new Variable();
    $var->question = $title;
    $var->explanation = $explanation;
    $var->tooltips = $tooltips;

    for($i=0; $i<count($qCodes); $i++){
        $ans = new Answer();
        $ans->code = $qCodes[$i];
        $ans->label = $labels[$i];
        $ans->lowCutoff = $lowCutoffs[$i];
        $ans->highCutoff = $highCutoffs[$i];
        $ans->totalCutoff = $totalCutoffs[$i];
        $var->answers[] = $ans;
    }
    return $var;
}

