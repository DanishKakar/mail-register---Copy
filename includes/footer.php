</main>
<footer class="app-footer">
    <p>د دینی جامعاتو او تخصصاتو ریاست &ndash; د ډیتابس امریت &copy; <?= date('Y') ?></p>
</footer>
<script src="<?= BASE_URL ?>assets/js/attachment-toggle.js"></script>
<script>

    function toggleAttachmentPages(checkboxId, boxId){

        const check = document.getElementById(checkboxId);

        const box = document.getElementById(boxId);

        function update(){

            box.style.display = check.checked ? 'block' : 'none';

        }

        check.addEventListener('change', update);

        update();

    }

    toggleAttachmentPages('ra','records_pages_box');

    toggleAttachmentPages('ea','exec_pages_box');

</script>
</body>
</html>