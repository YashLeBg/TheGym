# Arrêter les processus Node.js
Write-Host "Arrêt des processus Node.js..."
Get-Process -Name "node" -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue

# Attendre un moment pour que les processus se terminent
Start-Sleep -Seconds 2

# Supprimer le dossier .angular
Write-Host "Suppression du dossier .angular..."
if (Test-Path -Path ".angular") {
    try {
        Remove-Item -Recurse -Force ".angular" -ErrorAction SilentlyContinue
        Write-Host "Dossier .angular supprimé avec succès."
    } catch {
        Write-Host "Impossible de supprimer le dossier .angular. Certains fichiers sont peut-être encore verrouillés."
    }
}

# Supprimer le dossier node_modules
Write-Host "Suppression du dossier node_modules..."
if (Test-Path -Path "node_modules") {
    try {
        Remove-Item -Recurse -Force "node_modules" -ErrorAction SilentlyContinue
        Write-Host "Dossier node_modules supprimé avec succès."
    } catch {
        Write-Host "Impossible de supprimer le dossier node_modules. Certains fichiers sont peut-être encore verrouillés."
    }
}

# Réinstaller les dépendances
Write-Host "Réinstallation des dépendances..."
npm install

Write-Host "Nettoyage terminé. Vous pouvez maintenant exécuter 'ng serve'." 