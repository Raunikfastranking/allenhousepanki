<?php
/**
 * Excellence carousel card hover (DPS Amrapali pattern).
 * Scoped to .excellance-glide — bottom panel hidden until card hover.
 */
?>
    .excellance-glide .top-hide {
        position: relative;
        transform: translateY(0);
        transition: .2s all;
    }

    .excellance-glide .top-hide::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 150px;
        background: linear-gradient(180deg, #053469, transparent);
        border-radius: 8px;
        pointer-events: none;
    }

    .excellance-glide .top-hide h2 {
        transition: .2s ease-in-out;
    }

    .excellance-glide .card-top-peudo:hover .top-hide {
        transform: translateY(190px);
        opacity: 0;
        transition: transform 0.4s ease-in, opacity 0.4s ease-in 0.2s;
    }

    .excellance-glide .card-top-peudo:hover .top-hide h2 {
        opacity: 0;
    }

    .excellance-glide .bottom-card-content::after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 250px;
        z-index: -1;
        background: linear-gradient(180deg, #053469, #053469);
        opacity: .9;
    }

    .excellance-glide .bottom-card-content {
        transition: opacity .6s ease-in-out, visibility .6s ease-in-out;
        z-index: 2;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }

    .excellance-glide .card-top-peudo:hover .bottom-card-content {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }
