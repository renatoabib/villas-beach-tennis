<!-- [ footer apps ] start -->
<footer class="footer">
    <div class="container title mb-0">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-3">Fique por dentro</h2>
                <p class="mb-4 mb-md-0">
                    Cadastre seu e-mail para receber novidades, eventos e comunicados do clube.
                </p>
            </div>
            <div class="col-md-4">
                <div class="row">
                    <div class="col">
                        <input type="email" class="form-control" placeholder="Seu e-mail" />
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary">Inscrever</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-top">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <img src="{{ asset('assets/images/logo/villas.jpg') }}" alt="image"
                        class="footer-logo img-fluid mb-3" />
                    <p class="mb-4">Villas Beach Tennis &mdash; clube de beach tennis com estrutura
                        completa para sócios e atletas.</p>
                </div>
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-sm-4 my-3 my-sm-0">
                            <h5 class="mb-4">Company</h5>
                            <ul class="list-unstyled footer-link">
                                <li><a href="#" target="_blank">About</a></li>
                                <li><a href="#" target="_blank">Blog</a></li>
                                <li><a href="#" target="_blank">Team</a></li>
                                <li><a href="#" target="_blank">Free Version</a></li>
                            </ul>
                        </div>
                        <div class="col-sm-4 my-3 my-sm-0">
                            <h5 class="mb-4">Help & Support</h5>
                            <ul class="list-unstyled footer-link">
                                <li><a href="#" target="_blank">Documentation</a></li>
                                <li><a href="#" target="_blank">Contact us</a></li>
                                <li><a href="#" target="_blank">Support</a></li>
                                <li><a href="#" target="_blank">RoadMap</a></li>
                            </ul>
                        </div>
                        <div class="col-sm-4 my-3 my-sm-0">
                            <h5 class="mb-4">Useful Resources</h5>
                            <ul class="list-unstyled footer-link">
                                <li><a href="#" target="_blank">Terms & Condition</a></li>
                                <li><a href="#" target="_blank">Privacy Policy</a></li>
                                <li><a href="#" target="_blank">Licencse</a></li>
                                <li><a href="#" target="_blank">FAQ</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col my-1">
                    <p class="m-0">&copy; {{ date('Y') }} Villas Beach Tennis</p>
                </div>
                <div class="col-auto my-1">
                    <ul class="list-inline footer-sos-link mb-0">
                        <li class="list-inline-item">
                            <a href="#"><i class="ph-duotone ph-facebook-logo f-20"></i></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- [ footer apps ] End -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/highlight.min.js"></script>
<script src="{{ URL::asset('build/js/plugins/clipboard.min.js') }}"></script>
<script src="{{ URL::asset('build/js/component.js') }}"></script>
<script>
    // pc-component
    var elem = document.querySelectorAll('.component-list-card a');
    for (var l = 0; l < elem.length; l++) {
        var pageUrl = window.location.href.split(/[?#]/)[0];
        if (elem[l].href == pageUrl && elem[l].getAttribute('href') != '') {
            elem[l].classList.add('active');
        }
    }
    document.querySelector('#compo-menu-search').addEventListener('keyup', function() {
        var tval = document.querySelector('#compo-menu-search').value.toLowerCase();
        var elem = document.querySelectorAll('.component-list-card a');
        for (var l = 0; l < elem.length; l++) {
            var aval = elem[l].innerHTML.toLowerCase();
            var n = aval.indexOf(tval);
            if (n !== -1) {
                elem[l].style.display = 'block';
            } else {
                elem[l].style.display = 'none';
            }
        }
    });
</script>
