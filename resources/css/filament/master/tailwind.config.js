import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Master/**/*.php',
        './resources/views/filament/master/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}
