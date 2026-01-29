export ZSH="$HOME/.oh-my-zsh"
ZSH_THEME="agnoster"
DEFAULT_USER=$(whoami)

zstyle ':omz:update' mode disabled

plugins=(composer git laravel npm)

source $ZSH/oh-my-zsh.sh

alias c="clear"

alias ll="ls -lh"
alias la="ls -lhA"

alias cu="composer update"
alias ci="composer install"
alias cr="composer require"
alias cda="composer dump-autoload"

alias ar="php artisan"
alias mig="php artisan migrate"
alias mfs="php artisan migrate:fresh"
alias seed="php artisan db:seed"
alias opt="php artisan optimize"
alias cl="php artisan optimize:clear"

alias li="composer lint"
alias tu="composer test:unit"
alias tc="composer test:type-coverage"
alias tl="composer test:lint"
alias tt="composer test:types"
alias t="composer test"
