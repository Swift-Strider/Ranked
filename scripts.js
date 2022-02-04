const { execSync } = require("child_process");
const { platform } = require("os");

const commands = platform().startsWith("win") ? {
    phpstan: "vendor\\bin\\phpstan analyze src ",
    fmt: "vendor\\bin\\php-cs-fixer fix ",
} : {
    phpstan: "vendor/bin/phpstan analyze src ",
    fmt: "vendor/bin/php-cs-fixer fix ",
}

try {
    execSync(commands[process.argv[2]] + process.argv.slice(3).join(" "), {
        stdio: "inherit"
    });
} catch ($e) { /** Ignore execSync errors. */ }
