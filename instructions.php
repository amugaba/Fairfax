<style>
    .bluetext {
        font-weight: bold;
        color: #204d73;
    }
</style>
<div style="max-width:1000px; margin: 0 auto">
    <h3 style="text-align: center">How to Use the Data Explorer:</h3>
    <h4>1. Select the question you want to examine</h4>
    <div class="row">
        <ul><li>In the first row, click the <span class="bluetext">Select a question</span> drop-down to view all questions. You can type in this box to search through the list.</li>
            <ul><li>For example, type 'marijuana' to show only questions containing that word.</li></ul>
        <li>You can also filter the questions list by selecting a category in the <span class="bluetext">All categories</span> drop-down.</li>
            <ul><li>Select 'Drugs' to show only questions related to drugs.</li></ul>
        <li>After selecting a question, click <span class="bluetext">Generate Graph</span> to create your custom graph and data table.</li></ul>
    </div>
    <h4>2. Additional Options</h4>
    <div class="row">
        <ul><li>You optionally can select a second question in the second row. This will compare the first question to the second one.
            <ul><li>For instance, you can explore how cigarette use varies with alcohol use.</li></ul>
            <ul><li>If you want to compare the responses to questions of different demographic groups, add it as a second question. For instance, if you would like to compare ‘alcohol use’ among grade levels, select ‘alcohol’ in the first question and ‘grade’ in the second question.</li></ul>
            </li>
            <li>You can filter the data by selecting
                <?php if($dataset == '8to12'){ ?>grade, gender, race/ethnicity, sexual orientation, transgender status, or disability
                <?php } else { ?>gender, race/ethnicity, or disability<?php } ?>
                in the last row. This will show only data for the selected group(s).</li></ul>
    </div>
</div>

