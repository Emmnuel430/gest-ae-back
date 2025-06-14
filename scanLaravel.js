const fs = require("fs");
const path = require("path");

const baseDir = __dirname;
const outputFilePath = path.join(baseDir, "laravel_description.txt");

const ignoreDirs = [
    "node_modules",
    "vendor",
    "bootstrap",
    "config",
    "lang",
    "storage",
];

const categories = {
    Controllers: [],
    Models: [],
    Migrations: [],
    Seeders: [],
    Observers: [],
    Requests: [],
    Middleware: [],
    Routes: [],
    Views: [],
    Others: [],
};

function normalizePath(p) {
    return p.replace(/\\/g, "/");
}

function walk(dirPath) {
    const entries = fs.readdirSync(dirPath, { withFileTypes: true });

    entries.forEach((entry) => {
        const fullPath = path.join(dirPath, entry.name);
        const relativePath = normalizePath(path.relative(baseDir, fullPath));

        // Ignorer les répertoires spécifiques
        if (entry.isDirectory()) {
            const shouldIgnore = ignoreDirs.some((ignored) =>
                normalizePath(fullPath).includes(`/${ignored}`)
            );
            if (!shouldIgnore) {
                walk(fullPath);
            }
        }

        // Fichiers PHP
        else if (entry.isFile() && entry.name.endsWith(".php")) {
            const label = `Fichier PHP: ${relativePath}`;

            if (relativePath.includes("app/Http/Controllers")) {
                categories.Controllers.push(label);
            } else if (relativePath.includes("app/Models")) {
                categories.Models.push(label);
            } else if (relativePath.includes("app/Observers")) {
                categories.Observers.push(label);
            } else if (relativePath.includes("app/Http/Requests")) {
                categories.Requests.push(label);
            } else if (relativePath.includes("app/Http/Middleware")) {
                categories.Middleware.push(label);
            } else if (relativePath.includes("database/migrations")) {
                categories.Migrations.push(label);
            } else if (relativePath.includes("database/seeders")) {
                categories.Seeders.push(label);
            } else if (relativePath.includes("routes/")) {
                categories.Routes.push(label);
            } else if (relativePath.includes("resources/views")) {
                categories.Views.push(label);
            } else {
                categories.Others.push(label);
            }
        }
    });
}

function generateOutput() {
    const output = [];

    for (const [category, files] of Object.entries(categories)) {
        if (files.length) {
            output.push(`-${category}`);
            output.push(...files);
            output.push("--");
        }
    }

    fs.writeFileSync(outputFilePath, output.join("\n"), "utf-8");
    console.log(`✅ laravel_description.txt généré à : ${outputFilePath}`);
}

// Lancer
walk(baseDir);
generateOutput();
