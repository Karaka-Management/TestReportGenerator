<html>
    <head>
        <link rel="stylesheet" href="css/styles.css">
    </head>
    <body>
        <header>
            <div class="floater">
            </div>
        </header>
        <main>
            <div class="floater">
                <section id="introduction">
                    <h1>Test Report</h1>
                    <h2>Introduction</h2>
                    <h2>Structure of this document</h2>
                    <h2>Objective</h2>
                    <h2>Testing Process</h2>
                    <h2>Assessment & Evaluation</h2>
                </section>
                <section id="overview">
                    <table>
                        <thead>
                        <tbody>
                            <tr><th>Created<td><?= (new \DateTime('now'))->format('Y-m-d'); ?>
                            <tr><th>Version<td><?= $this->cmdData['version']; ?>
                    </table>
                </section>
                <section id="coverage">
                </section>
                <section id="tests">
                    <?php foreach ($this->testresult as $result) : ?>
                        <?php if ($result['type'] === 'testsuite') : ?>
                            <h2><?= $result['text']; ?></h2>
                        <?php else : ?>
                            <p><span class="status status-<?= $result['status']; ?>"><?= $this->getText(':status:' . $result['status']); ?></span><?= $result['info']['description']; ?> - <?= $result['time']; ?>s</p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </section>
            </div>
        </main>
        <footer>
            <div class="floater">
            </div>
        </footer>
    </body>
</html>