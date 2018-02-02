-- Module:Bootstrap
--
-- @since 1.1
--
-- @author Tobias Oetterer
local p = {}

function p.parse( frame )

    if not mw.bootstrap then
        return "mw.bootstrap module not found"
    end

	local component, input, args
	args = {}
	for k, v in pairs( frame.args ) do
		if ( component == nil ) then
			component = v
		elseif ( input == nil ) then
			input = v
		else
			args[k] = v
		end
	end

	return mw.bootstrap.parse( component, input, args )
end

function p.getSkin()

	if not mw.bootstrap then
		return "mw.bootstrap module not found"
	end

	return mw.bootstrap.getSkin()
end

return p
