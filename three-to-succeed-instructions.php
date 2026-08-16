<style>
    .bluetext {
        font-weight: bold;
        color: #204d73;
    }
</style>
<div style="max-width:1000px; margin: 0 auto">
    <h2 style="text-align: center">What is Three to Succeed?</h2>
    <p>The Three to Succeed concept is based on the Youth Survey analysis that shows that when children and youth have
        <span class="bluetext">three or more positive, protective factors/assets</span>
        in their lives, they are more likely to manage stress, make better choices, and develop healthy habits.</p>
    <p>The six assets were updated in 2025 to focus more on the positive influences from their communities, families, and schools.</p>
    <p>The graphs on this page show how <span class="bluetext">students' behaviors vary in relation to their number of assets</span>.
        For example, select the "Alcohol" category to see how the percentage of students that use alcohol changes based on their number of assets.</p>
    <div class="row">
        <h2 style="text-align: center">The Six Assets in this Graph Are (Starting in 2025):</h2>
        <div class="grid" style="font-size: 16px; line-height: 32px">
            <div class="grid-half">
                <ul class="spaced">
                    <li>Having adults in the community to talk to</li>
                    <li>Adults in the community let me know I am doing a good job</li>
                    <li>Parents or other adults in the family are available for help</li>
                </ul>
            </div>
            <div class="grid-half">
                <ul class="spaced">
                    <li>Parents or other adults in the family ask for input on most family decisions</li>
                    <li>Teachers let me know I am doing a good job</li>
                    <li>Having a trusted adult at school to ask for help</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row">
        <h2 style="text-align: center">Previously, the Six Assets Included (2024 and Earlier):</h2>
        <div class="grid" style="font-size: 16px; line-height: 32px">
            <div class="grid-half">
                <ul class="spaced">
                    <li>Can Ask Parents for Help with Personal Problems</li>
                    <li>Performs Community Service Once a Month or More</li>
                    <li>Feels It Is Important to Accept Responsibility for Actions</li>
                </ul>
            </div>
            <div class="grid-half">
                <ul class="spaced">
                    <li>Does Extracurricular Activities Once a Month or More</li>
                    <li>Teachers Recognize Good Work</li>
                    <li>Could Talk to Adults in Community about Something Important</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row" style="margin-top: 20px">
        <h2 style="text-align: center">How to Use This Page:</h2>
        <h4>1. Select the group or question you want to examine</h4>
        <div class="row">
            <ul class="spaced">
                <li>In the first row, click the <span class="bluetext">Select a question</span> drop-down to view all questions. You can type in this box to search through the list.</li>
                <ul><li>For example, type 'marijuana' to show only questions containing that word.</li></ul>
                <li>You can also filter the questions list by selecting a category in the <span class="bluetext">All categories</span> drop-down.</li>
                <ul><li>Select 'Drugs' to show only questions related to drugs.</li></ul>
                <li>After selecting a question, click <span class="bluetext">Generate Graph</span> to create your custom graph and data table.</li>
            </ul>
        </div>
        <h4>2. (Optional) Group Data</h4>
        <div class="row">
            <ul class="spaced">
                <li>You optionally can group the data by
                    <?php if($dataset == '8to12'){ ?>grade, gender, race/ethnicity, sexual orientation, transgender status, or disability.
                    <?php } else { ?>gender, race/ethnicity, or disability.<?php } ?>
                    The graph will display separate lines for each demographic group.</li>
                <ul><li>For example, you can select 'Gender' to see separate lines for Female and Male.</li></ul>
            </ul>
        </div>
    </div>
</div>

