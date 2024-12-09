<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .alert {
            margin-block: 2px !important;
        }
    </style>
</head>

<body class="bg-secondary">
    <div class="row mt-4 row d-flex justify-content-center align-items-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0">Import Csv File</h6>
                </div>
                <form id="upload-form" onsubmit="return false;" class="" method="post">
                    <div class="card-body justify-content-between align-items-center d-flex">
                        <div class="col-md-5">
                            <label for="file" style="cursor:pointer" class="d-inline-block">
                                <div style="border-radius:5px;border:1px dashed black;min-height:150px;min-width:170px"
                                    class="d-flex justify-content-center align-items-center">
                                    <span style="user-select:none; font-size: 12px">Upload csv file</span>
                                </div>
                            </label>
                            <div class="mt-3 bg-white d-none"
                                style="border:1px solid black;border-radius:5px;max-width:170px">
                                <div id="progress"
                                    class="align-items-center d-block d-flex justify-content-center bg-success"
                                    style="border-radius: 5px; height: 8px;width:0%;max-width:100%;transition: all 0.4s ease-out">
                                    <span id="prog-text" style="color:black;font-size: 6px"></span>
                                </div>
                            </div>
                            <input type="file" name="" accept=".csv" id="file"
                                class="d-none form-control form-control-sm">
                        </div>
                        <div class="overflow-hidden col-7 border" style="border-radius: 5px;height: 180px">
                            <span style="font-size: 12px" class="ms-2">Events</span>
                            <hr class="my-1">
                            <div id="events-container"
                                class="d-flex flex-column align-items-start h-100 pb-3 mb-3 overflow-auto"
                                style="max-height: 150px;scroll-behavior: instant">
                                <div class="row px-2 w-100" id="events">
                                    <span style="font-size:12px">Events will appear here...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-center">
                        <input id="submit" type="submit" value="Upload" class="btn btn-sm btn-primary">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    @vite(['resources/js/bootstrap.js'])
    <script>
        $(() => {
            var i = 1;


            $('#upload-form').on('submit', function(e) {

                e.preventDefault();
                $('#events').empty();

                const fileInput = $('#file')[0];
                if (fileInput.files.length === 0) {
                    $('#events').append(
                        `<span class="text-danger" style='font-size:12px'>${i++}. Please select file</span>`
                    );
                    return;
                }

                const file = fileInput.files[0];
                const channel = Math.random().toString(36).substr(2, 10);
                const formData = new FormData();
                formData.append('file', file);
                formData.append('name', channel);
                formData.append('_token', "{{ csrf_token() }}");

                $.ajax({
                    url: '/',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        $('#progress').parent().removeClass('d-none');
                        const xhr = new window.XMLHttpRequest();
                        var oldPercentComplete = 0;
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const percentComplete = Math.round((e
                                    .loaded / e.total) * 100);
                                $('#progress').css('width',
                                    percentComplete + "%")
                                if (oldPercentComplete != percentComplete &&
                                    percentComplete % 10 == 0) {
                                    $('#events').append(
                                        `<span class="text-success" style='font-size:12px'>${i++}. File Uploaded ${percentComplete}%</span>`
                                    );
                                    $('#events-container').scrollTop($(
                                        '#events-container').prop(
                                        'scrollHeight'));
                                }
                                if (percentComplete > 5) {
                                    $('#prog-text').text(
                                        `${percentComplete}%`);
                                }
                                oldPercentComplete = percentComplete;
                            }
                        });
                        return xhr;
                    },
                    success: function(response) {
                        // alert('Upload complete!');
                    },
                    error: function(e) {
                        let errorMessage = "An error occurred"; // Default message
                        if (e.responseJSON && e.responseJSON.message) {
                            errorMessage = e.responseJSON.message;
                        } else if (e.message) {
                            errorMessage = e.message;
                        } else if (e.statusText) {
                            errorMessage = e.statusText;
                        }
                        $('#events').append(
                            `<span class="text-danger" style='font-size:12px'>${i++}.  ${errorMessage}</span>`
                        );
                    },
                    beforeSend: function() {
                        $('#file').prop('disabled', true);
                        $('#submit').prop('disabled', true);
                        $('#events').append(
                            `<span class="text-info" style='font-size:12px'>${i++}. File uploading started...</span>`
                        );
                    },
                    complete: function() {
                        $('#file').prop('disabled', false);
                        $('#submit').prop('disabled', false);
                        Echo.channel(`csv-${channel}`)
                            .subscribed(() => {
                                console.log("Subscription succeeded!");
                            })
                            .listen(".csv-import-activity", async (e) => {
                                if (Number.isInteger(e.message)) {
                                    if ($('#percetage').length) {
                                        await $('#percetage').text(i + " CSV " + e
                                            .message + " % imported");
                                    } else {
                                        await $('#events').append(
                                            `<span id="percetage" class="text-warning" style='font-size:12px'>${i}. CSV ${e.message}% imported...</span>`
                                        );
                                    }
                                } else {
                                    $('#events').append(
                                        `<span class="text-danger" style='font-size:12px'>${i++}. ${e.message}...</span>`
                                    );
                                }
                            }).error((error) => {
                                console.error("Echo error:", error);
                            });
                    }
                });
            });

            $('#file').on('change', function() {
                $('#events').empty();
                $('#progress').parent().addClass('d-none');
                $('#prog-text').text(null);
                i = 1;
            }).on('click', function() {
                $('#events').empty();
                $('#progress').parent().addClass('d-none');
                $('#prog-text').text(null);
                i = 1;
            });
        });
    </script>
</body>

</html>
