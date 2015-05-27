        <script>
            $(".table.table-bordered.table-striped tbody tr").click(function() {
                if($(this).hasClass('highlight')) {
                    $(this).find("td input[type=checkbox]").prop('checked', false);
                    clickCheckbox();
                    $(this).removeClass('highlight');
                } else {
                    $(this).find("td input[type=checkbox]").prop('checked', true);
                    clickCheckbox();
                    $(this).addClass('highlight');
                }
            });
        </script>
        <footer class="navbar navbar-default navbar-fixed-bottom">
            <div class="container">
                <p class="navbar-text">Copyright © <?= date("Y") ?> - 
                Todos os Direitos Reservados - Homeobrás Postos Homeopáticos LTDA - Homeopatias.com</p>
            </div>
        </footer>
