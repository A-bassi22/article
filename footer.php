    </div> <!-- fermeture de main-content -->

    <script>
    function supprimerArticle(id) {
        if (confirm("Voulez-vous vraiment supprimer cet article ? Cette action est irréversible.")) {
            window.location.href = "supprimer_article.php?id=" + id;
        }
    }
    </script>
</body>
</html>
