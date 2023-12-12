create schema if not exists wishlist;
use wishlist;


create table if not exists themes (
    id int unsigned not null auto_increment primary key,
    user_id int unsigned,
    `name` varchar(100) not null,
    bg_color int4 unsigned,
    fg_color int4 unsigned
);
-- Foreign key constraint for user_id is added later


create table if not exists users (
    id int unsigned not null auto_increment primary key,
    username varchar(50) not null,
    email varchar(100) not null,
    pwd binary(32) not null,
    picture varchar(500),
    theme_id int unsigned,
    created_at timestamp not null default current_timestamp,

    visibility ENUM(
        'PRIVATE',
        'UNREFERENCED',
        'PUBLIC'
    ) not null,

    constraint fk_user_theme_id
        foreign key (theme_id) references themes(id)
        on delete set null
);


alter table themes add constraint fk_theme_user_id
    foreign key if not exists (user_id)
    references users(id)
    on delete restrict;


create table if not exists groups (
    id int unsigned not null auto_increment primary key,
    `name` varchar(100) not null,
    theme_id int unsigned,
    created_at timestamp not null default current_timestamp,

    visibility ENUM(
        'PRIVATE',
        'UNREFERENCED',
        'PUBLIC'
    ) not null,

    constraint fk_group_theme_id
        foreign key (theme_id) references themes(id)
        on delete set null
);


create table if not exists messages (
    id int unsigned not null auto_increment primary key,
    following_id int unsigned,
    group_id int unsigned not null,
    user_id int unsigned,
    content varchar(2000) not null,
    created_at timestamp not null default current_timestamp,
    edited_at timestamp,

    constraint fk_msg_following_id
        foreign key (following_id) references messages(id)
        on delete set null,

    constraint fk_msg_group_id
        foreign key (group_id) references groups(id)
        on delete cascade,

    constraint fk_msg_user_id
        foreign key (user_id) references users(id)
        on delete set null
);


-- Table to link users to groups
-- because their can be multiple users for one group
-- and multiple groups for one user
create table if not exists users_groups (
    id int unsigned not null auto_increment primary key,
    user_id int unsigned not null,
    group_id int unsigned not null,

    constraint fk_groups_user_id
        foreign key (user_id) references users(id)
        on delete restrict,

    constraint fk_users_group_id
        foreign key (group_id) references groups(id)
        on delete restrict
);


create table if not exists wishlists_users (
    id int unsigned not null auto_increment primary key,
    user_id int unsigned not null,

    access_right ENUM(
        'READ_ONLY',
        'READ_WRITE'
    ) not null,

    constraint fk_wishlist_user_id
        foreign key (user_id) references users(id)
        on delete restrict
);


create table if not exists wishlists_groups (
    id int unsigned not null auto_increment primary key,
    group_id int unsigned not null,

    access_right ENUM(
        'READ_ONLY',
        'READ_WRITE'
    ) not null,

    constraint fk_wishlist_group_id
        foreign key (group_id) references groups(id)
        on delete restrict
);


create table if not exists wishlists (
    id int unsigned not null auto_increment primary key,
    referenced_id int unsigned not null,
    theme_id int unsigned,
    title varchar(100) not null,
    position int not null,
    user_id int unsigned,
    group_id int unsigned,
    created_at timestamp not null default current_timestamp,

    visibility ENUM(
        'PRIVATE',
        'UNREFERENCED',
        'PUBIC'
    ) not null,

    constraint fk_wishlist_referenced_id
        foreign key (referenced_id) references wishlists(id)
        on delete restrict,

    constraint fk_wishlist_owner_group_id
        foreign key (group_id) references groups(id)
        on delete restrict,

    constraint fk_wishlist_owner_user_id
        foreign key (user_id) references users(id)
        on delete restrict,

    constraint fk_wishlist_theme_id
        foreign key (theme_id) references themes(id)
        on delete set null
);


create table if not exists categories (
    id int unsigned not null auto_increment primary key,
    wishlist_id int unsigned not null,
    title varchar(100) not null,
    `priority` int not null,
    position int not null,
    count boolean not null,

    constraint fk_category_wishlist_id
        foreign key (wishlist_id) references wishlists(id)
        on delete restrict
);


create table if not exists wishs (
    id int unsigned not null auto_increment primary key,
    referenced_id int unsigned,
    category_id int unsigned,
    title varchar(100),
    details varchar(1000),
    link varchar(500),
    img varchar(500),
    price float,
    quantity int,
    owneds int,
    position int,
    `priority` int,
    created_at timestamp not null default current_timestamp,

    `type` ENUM(
        'WISH',
        'UNIVERSE'
    ) not null,

    constraint fk_wish_referenced_id
        foreign key (referenced_id) references wishs(id)
        on delete restrict,

    constraint fk_wish_category_id
        foreign key (category_id) references categories(id)
        on delete restrict
);
