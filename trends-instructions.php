<style>
    .bluetext {
        font-weight: bold;
        color: #204d73;
    }
</style>
<div style="max-width:1000px; margin: 0 auto">
    <h3 style="text-align: center">How to Use the Trends Page:</h3>
    <h4>1. Select the question you want to examine</h4>
    <div class="row">
        <ul>
            <li>In the first row, click the <span class="bluetext">Select a question</span> drop-down to view all questions. You can type in this box to search through the list.</li>
            <ul><li>For example, type 'marijuana' to show only questions containing that word.</li></ul>
            <li>You can also filter the questions list by selecting a category in the <span class="bluetext">All categories</span> drop-down.</li>
            <ul><li>Select 'Drugs' to show only questions related to drugs.</li></ul>
            <li>After selecting a question, click <span class="bluetext">Generate Graph</span> to create your custom graph and data table.</li>
        </ul>
    </div>
    <h4>2. (Optional) Group Data</h4>
    <div class="row">
        <ul>
            <li>You optionally can group the data by
                <?php if($dataset == '8to12'){ ?>grade, gender, race/ethnicity, sexual orientation, or transgender status. <!-- TBD: Add disability next year -->
                <?php } else { ?>gender or race/ethnicity.<?php } ?>
                The graph will display separate lines for each demographic group.</li>
            <ul><li>For example, you can select 'Gender' to see separate lines for Female and Male.</li></ul>
        </ul>
    </div>
    <h3 style="text-align: center">Note: There was no survey conducted in 2020 due to the COVID-19 pandemic.</h3>
</div>

