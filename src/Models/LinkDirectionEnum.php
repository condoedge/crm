<?php

namespace Condoedge\Crm\Models;

/**
 * Direction of a link type, always read as: person1 is the {link type} of person2.
 *
 * Only PARENT_OF / CHILD_OF carry authority — they are what feed can_manage ownership
 * and getChildren(). PEER means "no authority", not "symmetric": an asymmetric peer
 * relation (grandparent/grandchild) gets its wording from opposite_id, not from here.
 */
enum LinkDirectionEnum: string
{
    case PARENT_OF = 'PARENT_OF';
    case CHILD_OF = 'CHILD_OF';
    case PEER = 'PEER';
}
