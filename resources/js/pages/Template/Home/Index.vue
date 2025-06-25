<script setup>
import AppLayout from '@/pages/Template/Layouts/AppLayouts.vue';
import { Link, router, Head } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const props = defineProps({
  programs: Array
});

const showProgram = (id) => {
  router.get(route('program.show', id));
};

const features = [
  {
    icon: "bi bi-gear",
    title: "Rapide & Agile",
    text: "Déploiement rapide et accompagnement flexible pour chaque porteur.",
  },
  {
    icon: "bi bi-window",
    title: "Sécurité & Structure",
    text: "Un cadre professionnel pour développer vos idées en toute confiance.",
  },
  {
    icon: "bi bi-headset",
    title: "Support Humain",
    text: "Une équipe dédiée pour vous accompagner à chaque étape.",
  },
];
</script>

<template>
  <AppLayout title="Accueil - DoniForm" :programs="[]">
    <!-- Section Hero -->
     <section id="hero" class="hero section">
      <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="mb-5 row align-items-center">
          <div class="mb-4 col-lg-6 mb-lg-0">
            <h1 class="mb-4 hero-title fw-bold">
              L'incubateur de référence au Mali.
            </h1>
            <p class="mb-4 hero-description text-muted">
              De l'idée à la croissance, nous marchons ensemble chaque étape
              <span class="d-block"
                >avec des programmes sur mesure et des mentors engagés.</span
              >
            </p>
            <div class="cta-wrapper">
              <Link :href="route('program.index')" class="btn btn-primary">Nos Formations</Link>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="hero-image">
              <img
                src="/assets/img/illustration/illustration-16.webp"
                alt="Business Growth"
                class="img-fluid"
                loading="lazy"
              />
            </div>
          </div>
        </div>

        <div class="m-1 row feature-boxes">
          <div
            class="col-lg-4"
            v-for="(feature, index) in features"
            :key="index"
            :data-aos="'fade-up'"
            :data-aos-delay="200 + index * 100"
          >
            <div class="p-4 shadow-sm feature-box">
              <div class="mb-3 feature-icon">
                <i :class="feature.icon + ' fs-2 text-accent'"></i>
              </div>
              <div class="feature-content">
                <h3 class="feature-title h5 fw-bold">{{ feature.title }}</h3>
                <p class="feature-text text-muted">{{ feature.text }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-5 programs-showcase position-relative">
      <div class="container"data-aos="fade-up" data-aos-delay="100">
        <div class="mb-5 text-center section-header">
          <h2 class="section-title fw-bold">
            Nos <span>Derniers Programmes</span>
          </h2>
          <p class="section-subtitle text-muted">
            Découvrez nos formations récentes
          </p>
        </div>

        <div class="row gy-4">
          <div class="col-md-4" v-for="program in programs" :key="program.id">
            <div class="p-4 border rounded shadow-sm service-item h-100">
              <div class="mb-3 service-icon">
                <i class="bi bi-lightbulb fs-1 text-accent"></i>
              </div>
              <div class="service-content">
                <h3 class="h5 fw-bold">{{ program.title }}</h3>
                <p class="text-muted">{{ program.description.substring(0, 100) }}...</p>
                <div class="mt-3 d-flex justify-content-between">
                  <button class="btn btn-outline-primary btn-sm" @click="showProgram(program.id)">Lire la suite...</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Bouton Voir plus -->
       <div class="mt-5 text-center" data-aos="fade-up">
          <Link
            :href="route('program.index')"
            class="btn btn-primary"
          >
            <span>Explorer tous nos programmes</span>
            <i class="bi bi-arrow-right"></i>
          </Link>
        </div>
      </div>
    </section>
  </AppLayout>
</template>

<style scoped>
.btn.btn-primary {
  color: #fff;
  background-color: #95b71d;
  border-color: #95b71d;
}

/* Optionnel : styles pour les états hover/focus */
.btn.btn-primary:hover,
.btn.btn-primary:focus {
  background-color: #85a71a;
  border-color: #85a71a;
}
.btn.btn-outline-primary {
  color: #95b71d;
  background-color: #fff;
  border-color: #95b71d;
}

/* Optionnel : styles pour les états hover/focus */
.btn.btn-outline-primary:hover,
.btn.btn-outline-primary:focus {
  background-color: #85a71a;
  border-color: #85a71a;
  color: #fff;
}
</style>