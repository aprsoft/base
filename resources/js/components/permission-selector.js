export default function permissionSelector(groups = []) {
    return {
        search: '',
        groups,
        selected() {
            return [...new Set((this.$wire.permissionIds || []).map(Number))];
        },
        selectedDetails(limit = 20) {
            const selected = new Set(this.selected());
            const details = [];
            for (const group of this.groups) {
                for (let i = 0; i < group.ids.length; i++) {
                    if (selected.has(group.ids[i])) {
                        details.push({id: group.ids[i], name: group.names[i]});
                        if (details.length >= limit) return details;
                    }
                }
            }
            return details;
        },
        group(module) {
            return this.groups.find(item => item.module === module);
        },
        selectedInGroup(module) {
            const group = this.group(module);
            if (!group) return 0;
            const selected = new Set(this.selected());
            return group.ids.filter(id => selected.has(id)).length;
        },
        allInGroup(module) {
            const group = this.group(module);
            return !!group && group.ids.length > 0 &&
                this.selectedInGroup(module) === group.ids.length;
        },
        toggleGroup(module) {
            const group = this.group(module);
            if (!group) return;
            const selected = new Set(this.selected());
            const remove = this.allInGroup(module);
            for (const id of group.ids) {
                if (remove) selected.delete(id);
                else selected.add(id);
            }
            this.$wire.$set('permissionIds', [...selected], false);
        },
        selectAll() {
            this.$wire.$set('permissionIds',
                this.groups.flatMap(group => group.ids), false);
        },
        clearAll() {
            this.$wire.$set('permissionIds', [], false);
        },
        normalize(value) {
            return String(value ?? '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLocaleLowerCase('es');
        },
        normalizedSearch() {
            return this.normalize(this.search.trim());
        },
        matchesPermission(module, name) {
            const term = this.normalizedSearch();
            if (!term) return true;
            const group = this.group(module);
            return [module, group?.label, name]
                .some(text => this.normalize(text).includes(term));
        },
        matchesGroup(module) {
            const group = this.group(module);
            return !!group && group.names
                .some(name => this.matchesPermission(module, name));
        },
        hasMatches() {
            return this.groups.some(group => this.matchesGroup(group.module));
        }
    };
}
