</main>
<footer class="app-footer">
    <p>د دینی جامعاتو او تخصصاتو ریاست &ndash; د معلوماتی ټکنالوژی امریت &copy; <?= date('Y') ?></p>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script>
$(document).ready(function(){

    $('.searchable-select').select2({
        width: '100%',
        placeholder: '-- انتخاب ریاست --',
        allowClear: true
    });

});
</script>


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