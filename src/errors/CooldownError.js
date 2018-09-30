module.exports = class CooldownError extends Error {
    constructor (cooldown) {
        super();

        this.message ='You must wait before download';
        this.cooldown = cooldown;
    }
};
