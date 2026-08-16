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
            <li>The question categories have been updated starting in 2025. The original categories used in 2024 and prior years remain the same.</li>
            <li>For some questions, direct comparison of some historical data (i.e., data from 2024 and earlier) with the data from 2025 and onwards for many variables is not
                recommended due to the changes in the question. Questions that were unchanged in the 2025 version of the survey are still comparable to historical data.
            </li>
        </ul>
    </div>

    <h2 style="text-align: center">How to Use the Data Explorer:</h2>
    <h3>1. Select the question you want to examine</h3>
    <div class="row">
        <ul class="spaced">
            <li>In the first row, click the <span class="bluetext">Select a question</span> drop-down to view all questions. You can type in this box to search through the list.
            </li>
            <ul>
                <li>For example, type 'marijuana' to show only questions containing that word.</li>
            </ul>
            <li>You can also filter the questions list by selecting a category in the <span class="bluetext">All categories</span> drop-down.</li>
            <ul>
                <li>Select 'Drugs' to show only questions related to drugs.</li>
            </ul>
            <li>After selecting a question, click <span class="bluetext">Generate Graph</span> to create your custom graph and data table.</li>
        </ul>
    </div>
    <h3>2. Additional Options</h3>
    <div class="row">
        <ul class="spaced">
            <li>You optionally can select a second question in the second row. This will compare the first question to the second one.</li>
            <ul class="spaced">
                <li>For instance, you can explore how cigarette use varies with alcohol use.</li>
                <li>If you want to compare the responses to questions of different demographic groups, add it as a second question. For instance, if you would like to compare
                    ‘alcohol use’ among grade levels, select ‘alcohol’ in the first question and ‘grade’ in the second question.
                </li>
            </ul>
            <li>You can filter the data by selecting
                <?php if ($dataset == '8to12') { ?>grade, gender, race/ethnicity, sexual orientation, transgender status, or disability
                <?php } else { ?>gender, race/ethnicity, or disability<?php } ?>
                in the last row. This will show only data for the selected group(s).
            </li>
        </ul>
    </div>
</div>

