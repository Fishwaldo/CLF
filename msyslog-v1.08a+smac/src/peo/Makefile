# $CoreSDI: Makefile.in,v 1.3.2.1.4.8 2001/11/20 09:56:24 alejo Exp $
#
# Copyright (c) 2001, Core SDI S.A., Argentina
# All rights reserved
# 
# Redistribution and use in source and binary forms, with or without
# modification, are permitted provided that the following conditions
# are met:
# 
# 1. Redistributions of source code must retain the above copyright
#    notice, this list of conditions and the following disclaimer.
# 2. Redistributions in binary form must reproduce the above copyright
#    notice, this list of conditions and the following disclaimer in the
#    documentation and/or other materials provided with the distribution.
# 3. Neither name of the Core SDI S.A. nor the names of its contributors
#    may be used to endorse or promote products derived from this software
#    without specific prior written permission.
#
# THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
# IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
# OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
# IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
# INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
# NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
# DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
# THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
# (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
# THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
#

CC=		gcc
CFLAGS=		-g -O2 -Wall
CPPFLAGS=	-I.. 

INSTALL=	/usr/bin/ginstall -c
INSTALL_DIR=	/usr/msyslog/sbin

PROG=		peochk
SRCS=		peochk.c hash.c md5c.c sha1.c rmd160.c
OBJS=		$(SRCS:.c=.o)

all: $(PROG) $(OBJS)

$(OBJS): $(SRCS)
	$(CC) $(CFLAGS) $(CPPFLAGS) -c $(SRCS)

$(PROG): $(OBJS)
	$(CC) $(CFLAGS) $(OBJS) -o $@

.PHONY: clean distclean
clean:
	-rm -f $(OBJS) core *.core $(PROG)

distclean: clean
	-rm -f Makefile

install:
	@if [ ! -d $(INSTALL_DIR) ]; then \
		mkdir -p $(INSTALL_DIR); \
	fi
	-$(INSTALL) ${PROG} $(INSTALL_DIR)

