<html>
    <head>
        <link rel="stylesheet" href="css/styles.css">
    </head>
    <body>
        <header>
            <div class="floater">
                <h1><?= $this->getText(':testreport'); ?></h1>
                <p><strong><?= $this->getText(':created'); ?>:</strong> <?= (new \DateTime('now'))->format('Y-m-d'); ?> - <strong><?= $this->getText(':version'); ?>:</strong> <?= $this->cmdData['version']; ?></p>
            </div>
        </header>
        <main>
            <div class="floater">
                <section id="introduction">
                    <h2><?= $this->getText(':introduction'); ?></h2>
                    <p><?= $this->getText(':introduction_desc_1'); ?></p>
                </section>
                <section id="toc">
                    <h2><?= $this->getText(':structure'); ?></h2>
                    <ul id="tocList">
                        <li><a href="#introduction"><?= $this->getText(':structure_link_1'); ?></a>
                        <li><a href="#toc"><?= $this->getText(':structure_link_2'); ?></a>
                        <li><a href="#objective"><?= $this->getText(':structure_link_3'); ?></a>
                        <li><a href="#testing_process"><?= $this->getText(':structure_link_4'); ?></a>
                        <li><a href="#testing_summary"><?= $this->getText(':structure_link_5'); ?></a>
                        <li><a href="#tests"><?= $this->getText(':structure_link_6'); ?></a>
                        <li><a href="#disclaimer"><?= $this->getText(':structure_link_9'); ?></a>
                    </ul>
                </section>
                <section id="objective">
                    <h2><?= $this->getText(':objective'); ?></h2>
                    <p><?= $this->getText(':objective_desc_1'); ?></p>
                </section>
                <section id="testing_process">
                    <h2><?= $this->getText(':testing_process'); ?></h2>
                    <p><?= $this->getText(':testing_process_desc_1'); ?></p>
                    <ul>
                        <li><?= $this->getText(':testing_process_list_1'); ?>
                        <li><?= $this->getText(':testing_process_list_2'); ?>
                        <li><?= $this->getText(':testing_process_list_3'); ?>
                        <li><?= $this->getText(':testing_process_list_4'); ?>
                        <li><?= $this->getText(':testing_process_list_5'); ?>
                    </ul>
                    <p><?= $this->getText(':testing_process_desc_2'); ?></p>
                </section>
                <section id="testing_summary">
                    <h2><?= $this->getText(':testing_summary'); ?></h2>
                    <p><?= $this->getText(':testing_summary_desc_1'); ?></p>

                    <h3><?= $this->getText(':testing_summary_coverage'); ?></h3>
                    <table>
                        <thead>
                            <tr>
                                <th><?= $this->getText(':description'); ?>
                                <th><?= $this->getText(':total'); ?>
                                <th><?= $this->getText(':covered'); ?>
                                <th><?= $this->getText(':uncovered'); ?>
                                <th><?= $this->getText(':ratio'); ?>
                        <tbody>
                            <tr>
                                <th><?= $this->getText(':methods'); ?>
                                <td><?= $this->methods; ?>
                                <td><?= $this->methodsCovered; ?>
                                <td><?= $this->methods - $this->methodsCovered; ?>
                                <td><?= number_format((float) (100 * ($this->methods !== 0 ? $this->methodsCovered / $this->methods : 1)), 1); ?>%
                            <tr>
                                <th><?= $this->getText(':statements'); ?>
                                <td><?= $this->statements; ?>
                                <td><?= $this->statementsCovered; ?>
                                <td><?= $this->statements - $this->statementsCovered; ?>
                                <td><?= number_format((float) (100 * ($this->statements !== 0 ? $this->statementsCovered / $this->statements : 1)), 1); ?>%
                            <tr>
                                <th><?= $this->getText(':conditionals'); ?>
                                <td><?= $this->conditionals; ?>
                                <td><?= $this->conditionalsCovered; ?>
                                <td><?= $this->conditionals - $this->conditionalsCovered; ?>
                                <td><?= number_format((float) (100 * ($this->conditionals !== 0 ? $this->conditionalsCovered / $this->conditionals : 1)), 1); ?>%
                    </table>

                    <h3><?= $this->getText(':testing_summary_tests'); ?></h3>
                    <table>
                        <thead>
                            <tr>
                                <th><?= $this->getText(':testing_summary_coverage'); ?>
                                <th><?= $this->getText(':total'); ?>
                                <th><?= $this->getText(':successful'); ?>
                                <th><?= $this->getText(':skipps'); ?>
                                <th><?= $this->getText(':warnings'); ?>
                                <th><?= $this->getText(':failures'); ?>
                                <th><?= $this->getText(':errors'); ?>
                        <tbody>
                            <tr>
                                <th><?= $this->getText(':test_suits'); ?>
                                <td><?= $this->suits; ?>
                                <td><?= $this->suits - $this->skippsSuits - $this->failuresSuits - $this->errorsSuits; ?><td><?= $this->skippsSuits; ?>
                                <td><?= $this->warningsSuits; ?>
                                <td><?= $this->failuresSuits; ?>
                                <td><?= $this->errorsSuits; ?>
                            <tr>
                                <th><?= $this->getText(':tests'); ?>
                                <td><?= $this->tests; ?>
                                <td><?= $this->tests - $this->skipps - $this->failures - $this->errors; ?>
                                <td><?= $this->skipps; ?>
                                <td><?= $this->warnings; ?>
                                <td><?= $this->failures; ?>
                                <td><?= $this->errors; ?>
                    </table>

                    <p><?= $this->getText(':testing_summary_desc_2'); ?> <strong><?= $this->assertions; ?></strong></p>
                    <p><?= $this->getText(':testing_summary_desc_3'); ?> <strong><?= $this->duration; ?>s</strong></p>
                </section>
                <section id="tests">
                    <h2><?= $this->getText(':tests'); ?></h2>
                    <?php foreach ($this->testresult as $result) : ?>
                        <?php if ($result['type'] === 'testsuite') : ?>
                            <h3><?= $result['info']['description']; ?></h3>
                        <?php else : ?>
                            <p><span class="status status-<?= $result['status']; ?>"><?= $this->getText(':status:' . $result['status']); ?></span><?= $result['info']['description']; ?> - <?= $result['time']; ?>s</p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </section>
                <section id="disclaimer">
                    <h2><?= $this->getText(':disclaimer'); ?></h2>
                    <p><?= $this->getText(':disclaimer_desc_1'); ?></p>
                </section>
            </div>
        </main>
        <footer>
            <div class="floater">
            </div>
        </footer>
    </body>
</html>