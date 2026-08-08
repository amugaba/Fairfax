<style>
    .bluetext {
        font-weight: bold;
        color: #204d73;
    }
</style>
<div style="max-width:1000px; margin: 0 auto">
    <div style="margin-bottom: 40px">
        <h2 style="text-align: center">Updates in 2025</h2>
        <ul style="font-size: 18px;" class="spaced">
            <li>Question Categories have been updated.</li>
            <li>For some questions, direct comparison of some historical data (i.e., data from 2024 and earlier) with the data from 2025 and onwards for many variables is not
                recommended due to the changes the question. Questions that were unchanged in the 2025 version of the survey are still comparable to historical data.
            </li>
        </ul>
    </div>

    <h2 style="text-align: center">How to Use the Trends Page:</h2>
    <h3>1. Select the question you want to examine</h3>
    <div class="row">
        <ul class="spaced">
            <li>In the first row, click the <span class="bluetext">Select a question</span> drop-down to view all questions. You can type in this box to search through the list.</li>
            <ul><li>For example, type 'marijuana' to show only questions containing that word.</li></ul>
            <li>You can also filter the questions list by selecting a category in the <span class="bluetext">All categories</span> drop-down.</li>
            <ul><li>Select 'Drugs' to show only questions related to drugs.</li></ul>
            <li>After selecting a question, click <span class="bluetext">Generate Graph</span> to create your custom graph and data table.</li>
        </ul>
    </div>
    <h3>2. (Optional) Group Data</h3>
    <div class="row">
        <ul class="spaced">
            <li>You optionally can group the data by
                <?php if($dataset == '8to12'){ ?>grade, gender, race/ethnicity, sexual orientation, or transgender status. <!-- TBD: Add disability next year -->
                <?php } else { ?>gender or race/ethnicity.<?php } ?>
                The graph will display separate lines for each demographic group.</li>
            <ul><li>For example, you can select 'Gender' to see separate lines for Female and Male.</li></ul>
        </ul>
    </div>
    <h4 style="text-align: center; margin-top: 30px">Note: There was no survey conducted in 2020 due to the COVID-19 pandemic.</h4>
</div>

