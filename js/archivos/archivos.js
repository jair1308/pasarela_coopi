(function ($, Drupal) {
    $(document).ready(function () {
        $(document).on('click', '#btn-cargar-archivo', function (e) {
            const fileInput = $('#formFile')[0];
            const file = fileInput.files[0];
    
            if (file) {
                const formData = new FormData();
                formData.append('archivosAdjuntos', file);
    
                $.ajax({
                    url: ApiRestURLS.cargarArchivo,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        Swal.fire({
                            title: "Facturas cargadas",
                            text: "Se cargaron "+response.lineasT+" facturas",
                            icon: "success"
                        });
                        window.location.reload();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Error al subir el archivo:', textStatus, errorThrown);
                    }
                });
            } else {
                console.error('No se ha seleccionado ningún archivo');
            }
        });
    });
  })(jQuery, Drupal);
  