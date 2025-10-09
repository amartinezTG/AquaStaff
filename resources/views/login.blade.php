@include('layout.shared')

<body>

  <main>
    <div class="container">

      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

              <div class="d-flex justify-content-center py-4">
                <a href="index.html" class="logo d-flex align-items-center w-auto">
                  <img src="assets/client/AQCC_Isotipo-03.png" alt="">
                  <span class="d-none d-lg-block">AquaAdmin</span>
                </a>
              </div><!-- End Logo -->

              <div class="card mb-3">
       

                <div class="card-body">

                  <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4">Ingrese a su cuenta</h5>
                    <p class="text-center small"></p>
                  </div>

                  <form class="row g-3 needs-validation" method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    @error('general')
                    <span class="small text-danger">
                      {{ $message }}
                    </span>
                    @enderror

                    <div class="col-12">
                      <label for="email" class="form-label">Usuario</label>
                      <span class="bi bi-question-circle" data-bs-toggle="tooltip" data-bs-placement="right" title="Campo requerido, es necesario que ingreses tu correo electrónico con el que registraste la cuenta."></span>
                      @error('email')
                          <br><span class="text-danger">{{ $message }}</span>
                        @enderror
                      <div class="input-group has-validation">
                       
                        <input type="text" name="email" class="form-control" value="{{ old('email') }}" id="email" required>
                        <div class="invalid-feedback">Requerido</div>
                      </div>
                    </div>

                    <div class="col-12">
                      <label for="yourPassword" class="form-label">Contraseña</label>
                      <input type="password" name="password" class="form-control" id="yourPassword" required>
                      <div class="invalid-feedback">Please enter your password!</div>
                    </div>

                    <div class="col-12 ">
                      @error('g-recaptcha-response')
                        <span class="text-danger">{{ $message }}</span>
                      @enderror
                      {{-- <div class="g-recaptcha" data-sitekey="{{ env('GOOGLE_RECAPTCHA_KEY') }}"></div>  --}}

                    </div>
                    
                    <div class="col-12">
                      <button class="btn btn-primary w-100" type="submit">Ingresar</button>
                    </div>
                    <div class="col-12">
                      
                    </div>
                  </form>

                </div>
              </div>

              <div class="credits">
                <!-- All the links in the footer should remain intact. -->
                <!-- You can delete the links only if you purchased the pro version. -->
                <!-- Licensing information: https://bootstrapmade.com/license/ -->
                <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
                Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a> -->
              </div>

            </div>
          </div>
        </div>

      </section>

    </div>
  </main><!-- End #main -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>


@include('layout.footer')