<div class="bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <h1 class="display-4 fw-bold">Welcome to CodeIgniter <?= CodeIgniter\CodeIgniter::CI_VERSION ?></h1>
                <button type="button" class="btn btn-primary btn-lg mt-3" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    Тест модальное окно
                </button>
            </div>
        </div>
    </div>
</div>


<footer class="bg-dark text-white mt-5">
    <div class="container py-4">
        <div class="row">
            <div class="col-md-12 text-center">
                <p class="mb-0">Environment: <?= ENVIRONMENT ?></p>
            </div>
        </div>
    </div>
</footer>

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Заголовок</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Модальное окно</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary">Сохранить изменения</button>
            </div>
        </div>
    </div>
</div>
