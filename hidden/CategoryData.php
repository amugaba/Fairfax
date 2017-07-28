<?php
/**
 * Provides variables for 2015 categories
 */
function getCategoryVariable($cat)
{
    $connector = "";
    if ($cat == 1) {
        $title = "Alcohol";
        $qCodes = ['A2A', 'A3A', 'A4'];
        $labels = ['Lifetime Alcohol Use', 'Past Month Alcohol Use', 'Binge Drinking (5+ Drinks in a Row)'];
        $tooltips = ['The % of students who reported at least 1 occasion of alcohol use ever in their lives.',
            'The % of students who reported at least 1 occasion of alcohol use in the past 30 days.',
            'The % of students who reported at least 1 occasion of drinking 5 or more drinks in a row in the past two weeks.'];
        $lowCutoffs = [2, 2, 2];
        $highCutoffs = [null, null, null];
        $totalCutoffs = [null, null, null];
        $explanation = "<p>The Youth Survey asks about use of a wide variety of licit and illicit substances.  The highlights page focuses on alcohol, the most commonly used substance by Fairfax County youth.</p>
        <p>To learn about other substances or to compare alcohol use with other behaviors, <a href='graphs.php'>Explore the Data</a>.</p>";
    }
    else if ($cat == 2) {
        $title = "Tobacco";
        $qCodes = ['T3', 'T4A', 'T5', 'T2'];
        $labels = ['Lifetime Cigarette Use', 'Past Month Cigarette Use', 'Past Month E-Cigarette Use', 'Past Month Smokeless Tobacco Use'];
        $tooltips = ['The % of students who reported at least 1 occasion of ciagarette use ever in their lives.',
            'The % of students who reported at least 1 occasion of cigarette use in the past 30 days.',
            'The % of students who reported at least 1 occasion of e-cigarette use in the past 30 days.',
            'The % of students who reported at least 1 occasion of smokeless tobacco use in the past 30 days.'];
        $lowCutoffs = [2, 2, 2, 2];
        $highCutoffs = [null, null, null, null];
        $totalCutoffs = [null, null, null, null];
        $explanation = "<p>The Youth Survey asks about use of a wide variety of licit and illicit substances.  The highlights page focuses on tobacco, including e-cigarettes.</p>
        <p>To learn about other substances or to compare tobacco use with other behaviors, <a href='graphs.php'>Explore the Data</a>.</p>";
    }
    else if ($cat == 3) {
        $title = "Drugs";
        $qCodes = ['A2A', 'A3A', 'A4', 'D2A'];
        $labels = ['Past Month Marijuana Use', 'Past Month Inhalant Use', 'Past Month Painkiller Use (without doctor\'s order)', 'Past Month Heroin Use'];
        $tooltips = ['The % of students who reported at least 1 occasion of marijuana use in the past 30 days.',
            'The % of students who reported at least 1 occasion of inhalant use in the past 30 days.',
            'The % of students who reported at least 1 occasion of painkiller use in the past 30 days without having a doctor\'s prescription.',
            'The % of students who reported at least 1 occasion of heroin use in the past 30 days.'];
        $lowCutoffs = [2, 2, 2, 2];
        $highCutoffs = [null, null, null, null];
        $totalCutoffs = [null, null, null, null];
        $explanation = "<p>The Youth Survey asks about use of a wide variety of licit and illicit substances.  The highlights page focuses on selected substances of interest to the Fairfax County community.</p>
        <p>To learn about other substances or to compare substance use with other behaviors, <a href='graphs.php'>Explore the Data</a>.</p>";
    } else if ($cat == 4) {
        $title = "Sexual Health";
        $qCodes = ['X1', 'X8'];
        $labels = ['Lifetime Sexual Intercourse', 'Lifetime Oral Sex'];
        $tooltips = ['The % of students who reported having sexual intercourse at least once in their lives.',
            'The % of students who reported having oral sex at least once in their lives.'];
        $lowCutoffs = [1, 1];
        $highCutoffs = [1, 1];
        $totalCutoffs = [null, null];
        $explanation = "<p>The Youth Survey asks about students' sexual behavior, including preventive behaviors (condom use).
        Related questions addressing aggression in relationships are reported in the <a href='highlights.php?cat=7'>Dating Aggression</a> category.</p>
        <p>To learn more about behaviors related to sexual health, <a href='graphs.php'>Explore the Data</a>.</p>";
    } else if ($cat == 5) {
        $title = "Vehicle Safety";
        $qCodes = ['A5', 'S3'];
        $labels = ['Driving after Drinking', 'Texting while Driving'];
        $tooltips = ['The % of students who reported at least 1 occasion of driving after drinking in the past 30 days.',
            'The % of students who reported at least 1 occasion of texting or e-mailing while driving a car or other vehicle in the past 30 days.'];
        $lowCutoffs = [3, 3];
        $highCutoffs = [null, null];
        $totalCutoffs = [null, null];
        $explanation = "<p>The Youth Survey asks about behaviors that are associated with unsafe driving practices, such as driving
        after drinking and texting while driving.</p><p style='font-style: italic; text-decoration: underline'>Data are for 12th grade students only.</p>
        <p>To compare vehicle safety with other behaviors, <a href='graphs.php'>Explore the Data</a>.</p>";
    } else if ($cat == 6) {
        $title = "Bullying and Cyberbullying";
        $qCodes = ['B20', 'B22', 'CB3', 'CB2'];
        $labels = ['Bullied Someone at School', 'Had Been Bullied at School', 'Cyberbullied Someone at School', 'Had Been Cyberbullied at School'];
        $tooltips = ['The % of students who reported having bullied someone on school property within the past 12 months.',
            'The % of students who reported having been bullied on school property within the past 12 months.',
            'The % of students who reported having cyberbullied someone in the past year.',
            'The % of students who reported having been cyberbullied in the past year.'];
        $lowCutoffs = [1, 1, 2, 2];
        $highCutoffs = [1, 1, null, null];
        $totalCutoffs = [null, null, null, null];
        $explanation = "<p>The Youth Survey asks questions about both bullying in-person and bullying online (called cyberbullying).</p>
        <p>Information specifically about bullying at school is available on the highlights page, while a broader range of activities (out-of-school behavior) is also available: <a href='graphs.php'>Explore the Data</a>.</p>";
        $connector = "they ";
    } else if ($cat == 7) {
        $title = "Dating Aggression";
        $qCodes = ['B15', 'B16'];
        $labels = ['Had a Partner that Always Wanted to Know Whereabouts', 'Had a Partner that Verbally Abused'];
        $tooltips = ['The % of students who reported ever having a partner in a dating or serious relationship who always wanted to know their whereabouts.',
            'The % of students who reported ever having a partner in a dating or serious relationship who called them names or put them down verbally.'];
        $lowCutoffs = [1, 1];
        $highCutoffs = [1, 1];
        $totalCutoffs = [null, null];
        $explanation = "<p>There are a variety of behaviors that might be classified as dating aggression, or that might signify 
            a risk of dating aggression. These range from a partner always wanting to know his or her partner's whereabouts to pressuring a partner to have sex.</p>
        <p>To learn more about behaviors related to dating aggression, including physical abuse, <a href='graphs.php'>Explore the Data</a>.</p>";
        $connector = "they ";
    } else if ($cat == 8) {
        $title = "Harassment and Aggressive Behaviors";
        $qCodes = ['B2A', 'B10A', 'B11', 'W5'];
        $labels = ["Insulted Someone's Race or Culture", 'Had Race or Culture Insulted', 'Had Been Sexually Harassed', 'Carried a Weapon'];
        $tooltips = ['The % of students who reported that they said something bad about someone’s race or culture in the past year.',
            'The % of students who reported that someone had said something bad about their race or culture in the past year.',
            'The % of students who reported being sexually harassed on at least 1 occasion in the past year.',
            'The % of students who reported carrying a weapon such as a gun, knife, or club in the past 30 days.'];
        $lowCutoffs = [2, 2, 2, 2];
        $highCutoffs = [null, null, null, null];
        $totalCutoffs = [null, null, null, null];
        $explanation = "<p>The Youth Survey asks about harassment and aggression in a variety of forms, both verbal and physical. The highlights page 
            provides information on racial/cultural harassment and sexual harassment. It also provides information on youth who reported carrying a weapon.</p>
        <p>To learn more about other behaviors and experiences related to harassment and aggression, <a href='graphs.php'>Explore the Data</a>.</p>";
        $connector = "they ";
    } else if ($cat == 10) {
        $title = "Nutrition and Physical Activity";
        $qCodes = ['fruitveg', 'H7', 'H3', 'H20', 'H2'];
        $labels = ['Ate Fruits and Vegetables at least 5 Times per Day', 'Drank No Soda during Past Week', 'Had One Hour of Physical Activity at least 5 Days per Week',
            'Get Eight or More Hours of Sleep on a School Night','Use Computer or Play Video Games for 3+ Hours per Day'];
        $tooltips = ['The % of students who ate fruits (excluding juice) and vegetables an average of 5 times per day over the past week.',
            'The % of students who did not drink soda (pop) in the past 7 days, not including diet soda.',
            'The % of students who reported being physically active for at least 60 minutes on at least 5 days in the past 7 days.',
            'The % of students who reported getting at least 8 hours of sleep on an average school night.',
            'The % of students who reported playing video or computer games or using a computer for something that was not school work at least 3 hours on an average school day.'];
        $lowCutoffs = [4.95, 1, 6, 5, 5];
        $highCutoffs = [null, 1, null, null, null];
        $totalCutoffs = [null, null, null, null, null];
        $explanation = "<p>The Youth Survey asks about eating fruits and vegetables, drinking sweetened beverages, level of physical activity, and other questions related to physical health.</p>
        <p>To learn more about behaviors related to nutrition and physical health, including unhealthy weight loss and food insecurity (hunger), <a href='graphs.php'>Explore the Data</a>.</p>";
        $connector = "they ";
    } else if ($cat == 11) {
        $title = "Mental Health";
        $qCodes = ['M5', 'M1', 'M2'];
        $labels = ['Had High Stress', 'Felt Sad or Hopeless for Two or More Weeks in a Row', 'Attempted Suicide'];
        $tooltips = ['The % of students who reported a stress level in the past month of 8 or higher on a scale from 1 to 10.',
            'The % of students who reported, during the past year, having felt so sad or hopeless almost every day for two weeks or more in a row that they stopped doing some usual activities.',
            'The % of students who reported having actually attempted suicide in the past 12 months.'];
        $lowCutoffs = [8, 1, 1];
        $highCutoffs = [null, 1, 1];
        $totalCutoffs = [null, null, null];
        $explanation = "<p>The Youth Survey asks about a variety of different aspects related to mental health. This page 
            highlights students who reported high levels of stress, those who felt sad or helpless two or more weeks in a row 
            (which may indicate risk for depression), and those who attempted suicide.</p>
        <p>To learn more about these topics, as well as suicidal ideation and unhealthy weight loss behaviors, <a href='graphs.php'>Explore the Data</a>.</p>";
        $connector = "they ";
    } else if ($cat == 12) {
        $title = "Civic Engagement and Time Use";
        $qCodes = ['C2', 'C11', 'C12', 'extracurric'];
        $labels = ['Volunteered to do Community Service Regularly', 'Did Homework for 3+ Hours per Day', 'Went to Work for 3+ Hours per Day', 'Did Extracurriculars for 3+ Hours per Day'];
        $tooltips = ['The % of students who reported volunteering regularly (did community service once a month or more during the past year).',
            'The % of students who reported doing 3+ hours of homework outside of school on an average school day.',
            'The % of students who reported doing 3+ hours of work (e.g., a job) on an average school day.',
            'The % of students who reported spending 3+ hours on extracurricular activities (both at school and away from school) on an average school day.'];
        $lowCutoffs = [4, 6, 6, 3];
        $highCutoffs = [null, null, null, null];
        $totalCutoffs = [null, null, null, null];
        $explanation = "<p>The Youth Survey asks questions related to civic engagement and use of time outside of school hours, 
            including volunteering for community service and time spent on homework, working at a job, and participating in extracurricular 
            activities. This page shows the percentage of students who volunteer regularly and spend 3 or more hours on selected activities outside of school hours.</p>
        <p>To see more specific level of engagement of students, such as number of hours worked or number of times volunteered, <a href='graphs.php'>Explore the Data</a>.</p>";
        $connector = "they ";
    } else if ($cat == 13) {
        $title = "Assets that Build Resiliency";
        $qCodes = ['PF9', 'PS3', 'PC2', 'PC11','LS4'];
        $labels = ['Parents Available to Help', 'Teacher Notices Good Job',
            'Adults in Community to Talk to', 'Availability of Extracurricular Activities','Accepting Responsibility for Actions and Mistakes'];
        $tooltips = ['The % of students who reported they could ask their parents for help with a personal problem.',
            'The % of students who reported their teachers notice when they do a good job and let them know.',
            'The % of students who reported that adults in their community are available to talk.',
            'The % of students who reported their community has after-school activities available.',
            'The % of students who reported that accepting responsibility is quite or extremely important.'];
        $lowCutoffs = [3, 3, 3, 3, null];
        $highCutoffs = [null, null, null, null, 2];
        $totalCutoffs = [null, null, null, null, null];
        $explanation = "<p>The Youth Survey asks about assets that are strengths in young people, their families, schools, and 
            communities that help them thrive in health, in school, and daily life, and in a safe environment.  The more assets an individual 
            has in his or her life, the fewer risk behaviors are reported.  This highlights page focuses on selected assets that build resiliency in youth.</p>
        <p>To learn about other assets or to compare prevalence of risk behaviors with assets, <a href='graphs.php'>Explore the Data</a> 
        under the following categories:  School, Family, Community Support, Civic Engagement, and Self/Peer Perception.</p>";
    } else {
        die("Category chosen is invalid.");
    }

    $var = new Variable();
    $var->question = $title;
    $var->explanation = $explanation;
    $var->tooltips = $tooltips;
    $var->connector = $connector;

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

