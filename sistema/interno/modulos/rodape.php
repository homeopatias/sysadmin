        <script>
            $(".table.table-bordered.table-striped tbody tr").click(function() {
                if($(this).hasClass('highlight')) {
                    $(this).removeClass('highlight');
                    $(this).find("td input[type=checkbox]").prop('checked', false);
                } else {
                    $(this).addClass('highlight');
                    $(this).find("td input[type=checkbox]").prop('checked', true);
                }
            });
        </script>
        <footer class="navbar navbar-default navbar-fixed-bottom">
            <div class="container">
                <p class="navbar-text">Copyright © <?= date("Y") ?> - 
                Todos os Direitos Reservados - Homeobrás Postos Homeopáticos LTDA - Homeopatias.com</p>
            </div>
        </footer>
